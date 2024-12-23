<?php declare(strict_types=1);

namespace PhpSlides\Src\Utils\Routes;

use PhpSlides\Exception;
use PhpSlides\Src\Foundation\Application;

trait StrictTypes
{
	private static array $types = [
		'INT',
		'BOOL',
		'JSON',
		'ALPHA',
		'ALNUM',
		'ARRAY',
		'FLOAT',
		'STRING',
		'INTEGER',
	];

	/**
	 *
	 * @param string[] $types
	 * @param string $haystack
	 * @return int|bool|float|array|string
	 */
	protected static function matchType(
		array $types,
		string $haystack,
	): int|bool|float|array|string|null {
		$typeofHaystack = self::typeOfString($haystack);

		foreach ($types as $type) {
			$type = $type === 'INTEGER' ? 'INT' : strtoupper(trim($type));

			if (!in_array($type, self::$types)) {
				throw new Exception(
					"{{$type}} is not recognized as a URL parameter type",
				);
			}

			if (strtoupper($type) === $typeofHaystack) {
				return match ($typeofHaystack) {
					'INT' => (int) $haystack,
					'BOOL' => (bool) $haystack,
					'FLOAT' => (float) $haystack,
					'ARRAY' => json_decode($haystack, true),
					default => $haystack,
				};
			}
		}

		return null;
	}

	/**
	 *
	 * @param string[] $types
	 * @param string $haystack
	 * @return int|bool|float|array|string
	 */
	protected static function matchStrictType(
		array $types,
		string $haystack,
	): int|bool|float|array|string {
		$types = array_map(fn($t) => strtoupper($t), $types);
		$typeofHaystack = self::typeOfString($haystack);

		if (!is_null($s = self::matchType($types, $haystack))) {
			return $s;
		}

		http_response_code(400);
		if (Application::$handleInvalidParameterType) {
			print_r((Application::$handleInvalidParameterType)($typeofHaystack));
			exit();
		} else {
			$requested = implode(', ', $types);
			throw new Exception(
				"Invalid request parameter type. {{$requested}} requested, but got {{$typeofHaystack}}",
			);
		}
	}

	private static function typeOfString(string $string)
	{
		$jd = json_decode($string, false);

		if (is_numeric($string)) {
			if (strpos($string, '.') !== false) {
				return 'FLOAT';
			} else {
				return 'INT';
			}
		} elseif (is_bool($string) || $string === 'true' || $string === 'false') {
			return 'BOOL';
		} elseif (ctype_alpha($string)) {
			return 'ALPHA';
		} elseif (ctype_alnum($string)) {
			return 'ALNUM';
		} elseif (json_last_error() === JSON_ERROR_NONE) {
			switch (gettype($jd)) {
				case 'object':
				   return 'JSON';
				case 'array':
				   return 'ARRAY';
				default:
				   return 'STRING';
			};
		} else {
			return 'STRING';
		}
	}
}
