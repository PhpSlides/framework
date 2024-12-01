<?php

namespace PhpSlides\Formatter\Views;

/**
 * Trait to format includes elements in PhpSlides view files.
 *
 * This trait modifies the custom `<!INCLUDE>` syntax in PhpSlides view files to PHP `component()` function calls,
 * handling the inclusion of files and passing attributes as parameters.
 */
trait FormatIncludes
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
	 * Replaces all includes elements in the view file contents.
	 *
	 * This method scans the contents for custom `<!INCLUDE>` or `<!INCLUDES>` elements,
	 * extracts the `path` and other attributes, and converts them into PHP `component()`
	 * function calls with the appropriate parameters.
	 */
	protected function includes()
	{
		// Regular expression pattern for matching the custom <INCLUDE> syntax
		$pattern = '/<!INCLUDE(S?)\s+([^>]+)\/?\/>/';

		$formattedContents = preg_replace_callback(
			$pattern,
			function ($matches) {
				$attributes = $matches[2]; // Extract the attributes: 'path="hello" name="value" id=1 role=["admin", "user"]'

				$pathPattern = '/path=["|\']([^"]+)["|\']/';
				$path = '';

				// Extract the 'path' attribute value using a regular expression
				$attributes = preg_replace_callback(
					$pathPattern,
					function ($matches) {
						global $path;
						$path = $matches[1];
						return null;
					},
					$attributes
				);

				global $path;

				// Format the other attributes into parameters, replacing '=' with ':'
				$param = preg_replace(
					'/(["\'])(?:\\.|[^\\1])*?\1(*SKIP)(*F)|\s+/',
					', ',
					trim($attributes)
				);
				$param = trim(str_replace('=', ': ', $param));

				// Return the formatted PHP include statement
				if (!empty($param)) {
					return '<' .
						"?php print_r(component(__DIR__ . '/$path', $param)) ?" .
						'>';
				}

				return '<' . "?php print_r(component(__DIR__ . '/$path')) ?" . '>';
			},
			$this->contents
		);

		// Update the contents with the formatted include statements
		$this->contents = $formattedContents;
	}
}
