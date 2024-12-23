<?php declare(strict_types=1);

namespace PhpSlides\Src\Utils;

trait Validate
{
	/**
	 * Validates and sanitizes the provided data.
	 *
	 * This method handles both individual values and arrays of values. It applies the appropriate validation
	 * and sanitization to each item in the array or to the single provided value.
	 * It also ensures that each item is validated according to its type.
	 *
	 * @param bool|float|int|string|array|null $data The data to validate. Can be a single value or an array of values.
	 *
	 * @return bool|float|int|string|array|null Returns the validated data, maintaining its original type(s).
	 * If an array is passed, an array of validated values is returned.
	 */
	protected function validate(
		bool|float|int|string|array|null $data,
	): bool|float|int|string|array|null {
		// If the data is an array, validate each item recursively
		if (is_array($data)) {
			return array_map(function ($item) {
				// Recursively validate each array element
				if (is_array($item)) {
					return $this->validate($item); // If item is array, call validate on it
				}
				return $this->realValidate($item); // Otherwise, validate the individual item
			}, $data);
		}

		// If the data is not an array, validate the value directly
		return $this->realValidate($data);
	}

	/**
	 * Performs the actual validation and sanitization of a single value.
	 *
	 * This method converts the value to a string, sanitizes it using `htmlspecialchars`, and then converts it
	 * back to its original type (boolean, integer, float, or string) based on the input type.
	 *
	 * @param bool|float|int|string|null $value The value to be validated and sanitized.
	 *
	 * @return bool|float|int|string|null The validated and sanitized value, converted back to its original type.
	 */
	private function realValidate(
		bool|float|int|string|null $value,
	): bool|float|int|string|null {
		if (!$value) {
			return null;
		}

		// Convert the value to string for sanitation
		$validatedValue = (string) $value;

		// Sanitize the string to prevent potential HTML injection issues
		$sanitizedValue = htmlspecialchars(
			trim($validatedValue),
			ENT_QUOTES,
			'UTF-8',
		);
		$type = gettype($value);

		// Convert the sanitized string back to its original type based on the initial value's type
		$convertedValue =
			is_bool($value) || $type === 'boolean'
				? (bool) $sanitizedValue
				: (is_numeric($value) || is_int($value) || $type === 'integer'
					? (is_double($value) ||
					is_float($value) ||
					$type === 'double' ||
					strpos((string) $value, '.') !== false
						? (float) $sanitizedValue
						: (int) $sanitizedValue)
					: $sanitizedValue);

		return $convertedValue;
	}
}
