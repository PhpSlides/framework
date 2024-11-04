<?php

namespace PhpSlides\Formatter\Views;

/**
 *
 */
trait FormatIncludes
{
	/**
	 *
	 */
	public function __construct()
	{
		// code...
	}

	/**
	 * Replaces all includes elements
	 */
	protected function includes()
	{
		$pattern = '/<!INCLUDE(S?)\s+([^>]+)\/?\/>/';
		/*$string =
		 '<!INCLUDES path="hello" name="value" id=\'1\' role=\'["admin", "user"]\' />';*/

		$formattedContents = preg_replace_callback(
			$pattern,
			function ($matches) {
				$attributes = $matches[2]; // This is the attributes part: 'path="hello" name="value" id=1 role=["admin", "user"]'

				$pathPattern = '/path=["|\']([^"]+)["|\']/';
				$path = '';

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
				$param = preg_replace(
					'/(["\'])(?:\\.|[^\\1])*?\1(*SKIP)(*F)|\s+/',
					', ',
					trim($attributes)
				);
				$param = trim(str_replace('=', ': ', $param));

				if (!empty($param)) {
					return '<' .
						"?php print_r(slides_include(__DIR__ . '/$path', $param)) ?" .
						'>';
				}
				return '<' .
					"?php print_r(slides_include(__DIR__ . '/$path')) ?" .
					'>';
			},
			$this->contents
		);

		$this->contents = $formattedContents;
	}
}
