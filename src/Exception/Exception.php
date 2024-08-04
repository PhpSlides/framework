<?php

namespace PhpSlides;

use Throwable;
use PhpSlides\Loader\FileLoader;
use Exception as DefaultException;
use Phpslides\Interface\SlidesException;

class Exception extends DefaultException implements Throwable, SlidesException
{
	public function getDetailedMessage(): string
	{
		$trace = $this->filterStackTrace();

		if (!empty($trace)) {
			$file = $trace[0]['file'];
			$line = $trace[0]['line'];
		} else {
			$file = $this->getFile();
			$line = $this->getLine();
		}

		return sprintf(
			'Error: %s in %s on line %d',
			$this->getMessage(),
			$file,
			$line
		);
	}

	public function filterStackTrace(): array
	{
		/**
		 * This Filter and removes all file path that is coming from the vendor folders
		 */
		$majorFilter = array_filter($this->getTrace(), function ($item) {
			$ss = strpos($item['file'], '/vendor/') === false;
			$sss = strpos($item['file'], '\vendor\\') === false;

			return $ss && $sss === true;
		});

		/**
		 * This filters and add only file path from the vendor folders
		 */
		$minorFilter = array_filter($this->getTrace(), function ($item) {
			$ss = strpos($item['file'], '/vendor/') !== false;
			$sss = strpos($item['file'], '\vendor\\') !== false;

			return $ss || $sss === true;
		});

		/**
		 * Create a new array and merge them together
		 * Major filters first
		 * Then the Minor filters follows
		 */
		$majorFilterValue = array_values($majorFilter);
		$minorFilterValue = array_values($minorFilter);
		$newFilter = array_merge($majorFilterValue, $minorFilterValue);

		return $newFilter;
	}

	public function getFilteredFile(): string
	{
		$trace = $this->filterStackTrace();

		if (!empty($trace)) {
			return $trace[0]['file'];
		}
		return $this->getFile();
	}

	public function getFilteredLine(): int
	{
		$trace = $this->filterStackTrace();
		if (!empty($trace)) {
			return $trace[0]['line'];
		}
		return $this->getLine();
	}

	public function getCodeSnippet($linesBefore = 10, $linesAfter = 10): array
	{
		$file = $this->getFilteredFile() ?? $this->getFile();
		$line = $this->getFilteredLine() ?? $this->getLine();

		(new FileLoader())->load(__DIR__ . '/../Globals/Chunks/codeSnippets.php');
		return getCodeSnippet(
			file: $file,
			line: $line,
			linesBefore: $linesBefore,
			linesAfter: $linesAfter
		);
	}
}
