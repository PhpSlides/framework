<?php

namespace PhpSlides\Formatter\Views;

/**
 *
 */
trait FormatBracketInterpolation
{
	/**
	 *
	 */
	public function __construct()
	{
		// code...
	}

	/**
	 * Replaces all Brackets interpolation
	 */
	protected function bracket_interpolation()
	{
		// Replace bracket interpolation {{! !}}
		$formattedContents = preg_replace_callback(
			'/{{!\s*(.*?)\s*!}}/',
			function ($matches) {
				return '';
			},
			$this->contents
		);

		// Replace bracket interpolation {{ }}
		$formattedContents = preg_replace_callback(
			'/{{\s*(.*?)\s*}}/',
			function ($matches) {
				return '<' . '?php print_r(' . $matches[1] . ') ?' . '>';
			},
			$formattedContents
		);

		$this->contents = $formattedContents;
	}
}
