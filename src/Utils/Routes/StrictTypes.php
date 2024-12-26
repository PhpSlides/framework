<?php declare(strict_types=1);

namespace PhpSlides\Core\Utils\Routes;

use PhpSlides\Exception;
use PhpSlides\Core\Utils\Routes\Exception\InvalidTypesException;

/**
 * Trait StrictTypes
 *
 * This trait is used to enforce strict type checking in the parameter types
 *
 * @package PhpSlides\Core\Utils\Routes
 */
trait StrictTypes
{
	/**
	 * Matches the type of a given string against an array of types.
	 *
	 * @param string $needle The string to check the type of.
	 * @param array $haystack The array of types to match against.
	 * @return bool Returns true if the type of the string matches any type in the array, false otherwise.
	 */
	protected static function matchType(string $needle, array $haystack): bool
	{
		$typeOfNeedle = self::typeOfString($needle);

		foreach ($haystack as $type) {
			$type = strtoupper(trim($type));
			$type = $type === 'INTEGER' ? 'INT' : $type;
			$type = $type === 'BOOLEAN' ? 'BOOL' : $type;

			if (self::matches($needle, $type)) {
				return true;
			}

			if (strtoupper($type) === $typeOfNeedle) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Matches the given string against a list of types and returns the value
	 * cast to the matched type.
	 *
	 * @param string $needle The string to match and cast.
	 * @param string[] $haystack The list of types to match against.
	 * @return int|bool|float|array|string The value cast to the matched type.
	 * @throws InvalidTypesException If the type of the needle does not match any type in the haystack.
	 */
	protected static function matchStrictType(
		string $needle,
		array $haystack,
	): int|bool|float|array|string {
		$types = array_map(fn($t) => strtoupper($t), $haystack);
		$typeOfNeedle = self::typeOfString($needle);

		if (self::matchType($needle, $types)) {
			return match ($typeOfNeedle) {
				'INT' => (int) $needle,
				'BOOL' => filter_var($needle, FILTER_VALIDATE_BOOLEAN),
				'FLOAT' => (float) $needle,
				'ARRAY' => json_decode($needle, true),
				default => $needle,
			};
		}

		InvalidTypesException::catchInvalidStrictTypes($haystack);
		throw InvalidTypesException::catchInvalidParameterTypes(
			$types,
			$typeOfNeedle,
		);
	}

	/**
	 * Matches the type of the given needle against the specified haystack type.
	 *
	 * This method checks if the type of the needle matches the type specified in the haystack.
	 * If the haystack specifies an array type, it will recursively check each element of the array.
	 *
	 * @param string $needle The value to check.
	 * @param string $haystack The type specification to match against.
	 * @return bool Returns true if the needle matches the haystack type, otherwise false.
	 * @throws InvalidTypesException If the needle does not match the haystack type.
	 */
	private static function matches(string $needle, string $haystack): bool
	{
		$haystack = preg_replace('/INTEGER<(.+)>/', 'INT<$1>', $haystack);

		$typeOfNeedle = self::typeOfString((string) $needle);
		$typeOfNeedle2 = $typeOfNeedle;
		$needle2 = $needle;

		/**
		 * MATCH ARRAY RECURSIVELY
		 */
		if (
			preg_match('/ARRAY<(.+)>/', $haystack, $matches) &&
			$typeOfNeedle === 'ARRAY'
		) {
			$needle = json_decode($needle, true);
			$eachArrayTypes = preg_split('/,(?![^<]*>)/', $matches[1]);

			if (!is_array($needle)) {
				$requested = implode(', ', $eachArrayTypes);
				throw InvalidTypesException::catchInvalidParameterTypes(
					$eachArrayTypes,
					$typeOfNeedle,
				);
			}

			foreach ($eachArrayTypes as $key => $eachArrayType) {
				$eachTypes = preg_split(
					'/\|(?![^<]*>)/',
					trim(strtoupper($eachArrayType)),
				);

				if (!isset($needle[$key])) {
					throw InvalidTypesException::catchInvalidParameterTypes(
						$eachTypes,
						'NULL',
						"Array index $key not found in the request parameter",
					);
				}

				$needle2 = is_array($needle[$key])
					? json_encode($needle[$key])
					: (string) $needle[$key];
				$typeOfNeedle2 = self::typeOfString($needle2);

				if (!self::matchType($needle2, $eachTypes)) {
					$requested = implode(', ', $eachTypes);
					InvalidTypesException::catchInvalidStrictTypes($eachTypes);
					throw InvalidTypesException::catchInvalidParameterTypes(
						$eachTypes,
						$typeOfNeedle2,
						"Invalid request parameter type: Expected {{$requested}} at array index {{$key}}, but received {{$typeOfNeedle2}}.",
					);
				}
			}
			return true;
		}

		/**
		 * MATCH INT<MIN, MAX>
		 */
		if (
			preg_match('/INT<(\d+)(?:,\s*(\d+))?>/', $haystack, $matches) &&
			$typeOfNeedle === 'INT'
		) {
			$min = (int) $matches[1];
			$max = (int) $matches[2] ?? null;
			$needle = (int) $needle;

			if (
				(!$max && $needle < $min) ||
				($max && ($needle < $min || $needle > $max))
			) {
				$requested = !$max ? "INT min($min)" : "INT min($min), max($max)";
				throw InvalidTypesException::catchInvalidParameterTypes(
					[$requested],
					(string) $needle,
				);
			}
			return true;
		}

		/**
		 * MATCH STRING<MIN, MAX> LENGTH
		 */
		if (
			preg_match('/STRING<(\d+)(?:,\s*(\d+))?>/', $haystack, $matches) &&
			$typeOfNeedle === 'STRING'
		) {
			$min = (int) $matches[1];
			$max = (int) $matches[2] ?? null;
			$needle = (int) strlen($needle);

			if (
				(!$max && $needle < $min) ||
				($max && ($needle < $min || $needle > $max))
			) {
				$requested = !$max
					? "STRING min($min)"
					: "STRING min($min), max($max)";
				throw InvalidTypesException::catchInvalidParameterTypes(
					[$requested],
					(string) $needle,
				);
			}
			return true;
		}

		/**
		 * MATCH ENUM TYPE
		 */
		if (preg_match('/ENUM<(.+)>/', $haystack, $matches)) {
			$needle = strtoupper($needle);
			$enum = array_map(fn($e) => trim($e), explode('|', $matches[1]));

			if (!in_array($needle, $enum)) {
				$requested = implode(', ', $enum);

				throw InvalidTypesException::catchInvalidParameterTypes(
					$enum,
					$needle,
					"Invalid request parameter type: Expected an enum of type {{$requested}}, but received {{$needle}}.",
				);
			}
			return true;
		}

		InvalidTypesException::catchInvalidStrictTypes($haystack);
		return false;
	}

	/**
	 * Determines the type of a given string.
	 *
	 * This method analyzes the input string and returns a string representing its type.
	 * The possible return values are:
	 * - 'FLOAT' if the string represents a floating-point number.
	 * - 'INT' if the string represents an integer.
	 * - 'BOOL' if the string represents a boolean value.
	 * - 'JSON' if the string is a valid JSON object.
	 * - 'ARRAY' if the string is a valid JSON array.
	 * - 'STRING' if the string does not match any of the above types.
	 *
	 * @param string $string The input string to be analyzed.
	 * @return string The type of the input string.
	 */
	protected static function typeOfString(string $string): string
	{
		$decoded = json_decode($string, false);

		if (is_numeric($string)) {
			return strpos($string, '.') !== false ? 'FLOAT' : 'INT';
		} elseif (
			filter_var(
				$string,
				FILTER_VALIDATE_BOOLEAN,
				FILTER_NULL_ON_FAILURE,
			) !== null
		) {
			return 'BOOL';
		} elseif (json_last_error() === JSON_ERROR_NONE) {
			return match (gettype($decoded)) {
				'object' => 'JSON',
				'array' => 'ARRAY',
				default => 'STRING',
			};
		}

		return 'STRING';
	}
}
