<?php declare(strict_types=1);

namespace PhpSlides;

use PhpSlides\Loader\FileLoader;
use Exception as DefaultException;
use PhpSlides\Interface\SlidesException;

/**
 * The Exception class provides enhanced exception handling for the PhpSlides application.
 */
class Exception extends DefaultException implements SlidesException
{
	/**
	 * Get a detailed error message including file and line number.
	 *
	 * @return string A detailed error message.
	 */
	public function getDetailedMessage (): string
	{
		$trace = $this->filterStackTrace();

		if (!empty($trace))
		{
			$file = $trace[0]['file'];
			$line = $trace[0]['line'];
		}
		else
		{
			$file = $this->getFile();
			$line = $this->getLine();
		}

		return sprintf(
		 'Error: %s in %s on line %d',
		 $this->getMessage(),
		 $file,
		 $line,
		);
	}

	/**
	 * Filter the stack trace to remove paths from vendor directories.
	 *
	 * @return array The filtered stack trace.
	 */
	public function filterStackTrace (): array
	{
		/**
		 * This filter removes all file paths that come from the vendor folders.
		 */

		/*
			 $majorFilter = array_filter($this->getTrace(), function ($item) {
				 $ss = strpos($item['file'], '/vendor/') === false;
				 $sss = strpos($item['file'], '\vendor\\') === false;

				 return $ss && $sss === true;
			 });
			 */

		/**
		 * This filter adds only file paths from the vendor folders.
		 */

		/*
			 $minorFilter = array_filter($this->getTrace(), function ($item) {
				 $ss = strpos($item['file'], '/vendor/') !== false;
				 $sss = strpos($item['file'], '\vendor\\') !== false;

				 return $ss || $sss === true;
			 });
			 */

		/**
		 * Create a new array and merge them together.
		 * Major filters first, then the minor filters.
		 */

		/*
			 $majorFilterValue = array_values($majorFilter);
			 $minorFilterValue = array_values($minorFilter);
			 $newFilter = array_merge($majorFilterValue, $minorFilterValue);
			 */

		/**
		 * Replace generated views files to the corresponding view
		 */
		$newFilter = array_map(function ($item)
		{
			$item['file'] = str_replace('.g.php', '.php', $item['file']);
			$item['file'] = str_replace('.g.psl', '.psl', $item['file']);

			return $item;
		}, $this->getTrace());

		return $newFilter;
	}

	/**
	 * Get the file path from the filtered stack trace.
	 *
	 * @return string The file path.
	 */
	public function getFilteredFile (): string
	{
		$trace = $this->filterStackTrace();

		if (!empty($trace))
		{
			return $trace[0]['file'];
		}
		return $this->getFile();
	}

	/**
	 * Get the line number from the filtered stack trace.
	 *
	 * @return int The line number.
	 */
	public function getFilteredLine (): int
	{
		$trace = $this->filterStackTrace();
		if (!empty($trace))
		{
			return $trace[0]['line'];
		}
		return $this->getLine();
	}

	/**
	 * Get a code snippet surrounding the error line.
	 *
	 * @param int $linesBefore The number of lines before the error line to include.
	 * @param int $linesAfter The number of lines after the error line to include.
	 * @return array The code snippet.
	 */
	public function getCodeSnippet ($linesBefore = 10, $linesAfter = 10): array
	{
		$file = $this->getFilteredFile() ?? $this->getFile();
		$line = $this->getFilteredLine() ?? $this->getLine();

		(new FileLoader())->load(__DIR__ . '/../Globals/Chunks/codeSnippets.php');
		return getCodeSnippet(
		 file: $file,
		 line: $line,
		 linesBefore: $linesBefore,
		 linesAfter: $linesAfter,
		);
	}
}
