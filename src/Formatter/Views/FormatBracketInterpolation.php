<?php

namespace PhpSlides\Core\Formatter\Views;

/**
 * Trait for handling bracket interpolation in view contents.
 *
 * This trait is responsible for replacing bracket interpolation patterns like
 * `{{! ... !}}` and `{{ ... }}` with PHP code for outputting values.
 */
trait FormatBracketInterpolation
{
	/**
	 * Constructor.
	 *
	 * This constructor can be used to initialize any necessary values for the class
	 * that uses this trait. It currently doesn't perform any operations.
	 */
	public function __construct ()
	{
		// code...
	}

	/**
	 * Replaces bracket interpolation patterns in the view contents.
	 *
	 * This method looks for bracket interpolation patterns:
	 * - `{{! ... !}}`: Replaces with an empty string (effectively removes the content).
	 * - `{{ ... }}`: Replaces with PHP `print_r()` to output the value within the braces.
	 *
	 * After processing, it updates the `$this->contents` property with the modified content.
	 */
	protected function bracket_interpolation ()
	{
		// Replace bracket interpolation {{! ... !}}
		$formattedContents = preg_replace(
		 '/\{\{!\s*.*?\s*!\}\}/s',
		 '',
		 $this->contents,
		);

		// Replace bracket interpolation {{ ... }}
		$formattedContents = preg_replace_callback(
		 '/\{\{\s*(.*?)\s*\}\}/s',
		 function ($matches)
		 {
			 $val = trim($matches[1], ';');
			 return '<' . '?php print_r(' . $val . '); ?' . '>';
		 },
		 $formattedContents,
		);

		$this->contents = $formattedContents;
	}
}
