<?php

namespace PhpSlides\Formatter\Views;

/**
 *
 */
trait FormatImportQuotes
{
	/**
	 *
	 */
	public function __construct()
	{
		// code...
	}

	/**
	 * Format import quotes
	 */
	protected function import_quotes()
	{
		$formattedContents = str_replace(
			'import(\'',
			'import(__DIR__ . \'/',
			$this->contents
		);
		$formattedContents = str_replace(
			'import("',
			'import(__DIR__ . "/',
			$formattedContents
		);

		$this->contents = $formattedContents;
	}
}
