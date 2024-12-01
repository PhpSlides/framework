<?php declare(strict_types=1);

namespace PhpSlides\Formatter;

trait Validate
{
	/**
	 * Validates and sanitizes the provided data.
	 *
	 * This method handles both individual values and arrays of values. It applies the appropriate validation
	 * and sanitization to each item in the array or to the single provided value.
	 * It also ensures that each item is validated according to its type.
	 *
	 * @param bool|float|int|string|array $data The data to validate. Can be a single value or an array of values.
	 *
	 * @return bool|float|int|string|array Returns the validated data, maintaining its original type(s).
	 * If an array is passed, an array of validated values is returned.
	 */
	protected function validate(
		bool|float|int|string|array $data,
	): bool|float|int|string|array {
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
	 * @param bool|float|int|string $value The value to be validated and sanitized.
	 *
	 * @return bool|float|int|string The validated and sanitized value, converted back to its original type.
	 */
	private function realValidate(
		bool|float|int|string $value,
	): bool|float|int|string {
		// Convert the value to string for sanitation
		$validatedValue = (string) $value;

		// Sanitize the string to prevent potential HTML injection issues
		$sanitizedValue = htmlspecialchars(
			trim($validatedValue),
			ENT_QUOTES,
			'UTF-8',
		);

		// Convert the sanitized string back to its original type based on the initial value's type
		switch (gettype($value)) {
			case 'integer':
				$convertedValue = (int) $sanitizedValue;
				break;
			case 'double':
				$convertedValue = (float) $sanitizedValue;
				break;
			case 'boolean':
				$convertedValue = (bool) $sanitizedValue;
				break;
			default:
				// Default is string type (value remains a string)
				$convertedValue = $sanitizedValue;
		}

		// Return the converted value
		return $convertedValue;
	}
}
