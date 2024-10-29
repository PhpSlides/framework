<?php

namespace PhpSlides\Formatter;

trait Validate
{
	/**
	 * Validate and get the type of value
	 */
	protected function validate(
		bool|float|int|string|array $data
	): bool|float|int|string|array {
		if (is_array($data)) {
			return array_map(function ($item) {
				if (is_array($item)) {
					return $this->validate($item);
				}
				return $this->realValidate($item);
			}, $data);
		}
		return $this->realValidate($data);
	}

	private function realValidate(
		bool|float|int|string $value
	): bool|float|int|string {
		// Convert to string for validation
		$validatedValue = (string) $value;

		// Sanitize the string using htmlspecialchars
		$sanitizedValue = htmlspecialchars(trim($validatedValue), ENT_NOQUOTES);

		// Convert back to original type using gettype
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
				$convertedValue = $sanitizedValue; // remains a string
		}

		return $convertedValue;
	}
}
