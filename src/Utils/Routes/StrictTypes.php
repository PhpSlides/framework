<?php declare(strict_types=1);

namespace PhpSlides\Src\Utils\Routes;

use PhpSlides\Src\Utils\Routes\Exception\InvalidTypesException;

/**
 * Trait StrictTypes
 *
 * This trait is used to enforce strict type checking in the parameter types
 *
 * @package PhpSlides\Src\Utils\Routes
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
	protected static function matchType (string $needle, array $haystack): bool
	{
		$typeOfNeedle = self::typeOfString($needle);

		foreach ($haystack as $type)
		{
			$type = strtoupper(trim($type));
			$type = $type === 'INTEGER' ? 'INT' : $type;
			$type = $type === 'BOOLEAN' ? 'BOOL' : $type;

			if (self::matches($needle, $type))
			{
				return true;
			}

			if (strtoupper($type) === $typeOfNeedle)
			{
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
	protected static function matchStrictType (
	 string $needle,
	 array $haystack,
	): int|bool|float|array|string {
		$types = array_map(fn ($t) => strtoupper($t), $haystack);
		$typeOfNeedle = self::typeOfString($needle);

		if (self::matchType($needle, $types))
		{
			return match ($typeOfNeedle)
			{
				  'INT' => (int) $needle,
				  'BOOL' => (bool) $needle,
				  'FLOAT' => (float) $needle,
				  'ARRAY' => json_decode($needle, true),
				  default => $needle,
			};
		}

		throw InvalidTypesException::catchInvalidParameterTypes($types, $typeOfNeedle);
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
	private static function matches (string $needle, string $haystack): bool
	{
		$typeOfNeedle = self::typeOfString((string) $needle);
		$typeOfNeedle2 = $typeOfNeedle;
		$needle2 = $needle;

		/**
		 * MATCH ARRAY RECURSIVELY
		 */
		if (
		preg_match('/ARRAY<(.+)>/', $haystack, $matches) &&
		$typeOfNeedle === 'ARRAY'
		)
		{
			$needle = json_decode($needle, true);
			$eachArrayTypes = explode(',', $matches[1]);

			foreach ($eachArrayTypes as $key => $eachArrayType)
			{
				$needle2 = is_array($needle[$key])
				 ? json_encode($needle[$key])
				 : (string) $needle[$key];

				$eachTypes = preg_split('/\|(?![^<]*>)/', trim($eachArrayType));
				$typeOfNeedle2 = self::typeOfString($needle2);

				if (!self::matchType($needle2, $eachTypes))
				{
					$requested = implode(', ', $eachTypes);
					InvalidTypesException::catchInvalidStrictTypes($eachTypes);
					throw InvalidTypesException::catchInvalidParameterTypes(
					 $eachTypes,
					 $typeOfNeedle2,
					 "Invalid request parameter type. {{$requested}} requested on array index $key, but got {{$typeOfNeedle2}}",
					);
				}
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
	 * - 'BOOL' if the string represents a boolean value ('true' or 'false').
	 * - 'ALPHA' if the string contains only alphabetic characters.
	 * - 'ALNUM' if the string contains only alphanumeric characters.
	 * - 'JSON' if the string is a valid JSON object.
	 * - 'ARRAY' if the string is a valid JSON array.
	 * - 'STRING' if the string does not match any of the above types.
	 *
	 * @param string $string The input string to be analyzed.
	 * @return string The type of the input string.
	 */
	protected static function typeOfString (string $string): string
	{
		$jd = json_decode($string, false);

		if (is_numeric($string))
		{
			if (strpos($string, '.') !== false)
			{
				return 'FLOAT';
			}
			else
			{
				return 'INT';
			}
		}
		elseif (is_bool($string) || $string === 'true' || $string === 'false')
		{
			return 'BOOL';
		}
		elseif (ctype_alpha($string))
		{
			return 'ALPHA';
		}
		elseif (ctype_alnum($string))
		{
			return 'ALNUM';
		}
		elseif (json_last_error() === JSON_ERROR_NONE)
		{
			return match (gettype($jd))
			{
				  'object' => 'JSON',
				  'array' => 'ARRAY',
				  default => 'STRING',
			};
		}
		else
		{
			return 'STRING';
		}
	}
}