<?php

namespace PhpSlides\Src\Formatter\Views;

/**
 * Trait to replace PhpSlides default tags in view files.
 *
 * This trait handles the replacement of the custom PhpSlides tags (like `<?` and `?>`)
 * in the view files, ensuring that the content inside them is correctly formatted
 * and ready for execution as PHP code.
 */
trait FormatPslTags
{
	/**
	 * Constructor.
	 *
	 * This constructor is a placeholder for any necessary initialization for
	 * the class using this trait. It currently does not perform any operations.
	 */
	public function __construct()
	{
		// code...
	}

	/**
	 * Replaces PhpSlides default tags in the view file contents.
	 *
	 * This method scans the contents of the view file and replaces instances of
	 * `<?` with properly formatted PHP code blocks. It trims the content within the
	 * PHP tags to remove unnecessary semicolons and ensures the code is correctly
	 * formatted for execution.
	 */
	protected function psl_tags()
	{
		// Regular expression to match any PHP opening and closing tags
		$formattedContents = preg_replace_callback(
			'/<' . '\?' . '\s+([\s\S]*?)\s*\?' . '>/s',
			function ($matches) {
				// Trim the content inside the PHP tags, removing trailing semicolons
				$val = trim($matches[1]);
				$val = str_ends_with(')', $val) ? $val . ';' : $val;

				// Reformat the PHP content and return it
				return '<' . '?php ' . $val . ' ?' . '>';
			},
			$this->contents,
		);

		// Update the contents with the formatted PHP code blocks
		$this->contents = $formattedContents;
	}
}
