<?php

namespace PhpSlides\Formatter\Views;

/**
 *
 */
trait FormatPslTags
{
	/**
	 *
	 */
	public function __construct()
	{
		// code...
	}

	/**
	 * Replaces PhpSlides default tags
	 */
	protected function psl_tags()
	{
		// replace <? elements
		$formattedContents = preg_replace_callback(
			'/<' . '\?' . '\s+([^?]*)\?' . '>/s',
			function ($matches) {
				$val = trim($matches[1]);
				$val = trim($val, ';');
				return '<' . '?php ' . $val . ' ?' . '>';
			},
			$this->contents
		);

		$this->contents = $formattedContents;
	}
}
