<?php

namespace PhpSlides\Loader;

use PhpSlides\Foundation\Application;

class FileLoader
{
	private array $result;

	/**
	 * Load File, and include it in your project
	 *
	 * @return self
	 */
	public function load($file): self
	{
		/**
		 * Root Directory for PhpSlides Project
		 */
		$basePath = Application::$basePath;

		/**
		 * Checks if File exists
		 */
		if (file_exists($file)) {
			$result = include $file;
			$this->result[] = $result;

			return $this;
		} else {
			throw new Exception("File not found: $filePath");
		}
	}

	/**
	 * Get Included File Result
	 */
	public function getLoad()
	{
		return $this->result;
	}

	/**
	 * Get File Contents
	 * Get Contents as String
	 *
	 * @return string File content as `string` and if no content, returns empty `string`
	 */
	public function getFileContents(string $file): string
	{
		/**
		 * Checks if File exists
		 */
		if (file_exists($file)) {
			/**
			 * Store the file content and clear cache
			 */
			ob_start();
			include $file;
			$output = ob_get_clean();

			if ($output !== false && strlen($output ?? '') > 0) {
				return $output;
			} else {
				return '';
			}
		} else {
			throw new Exception("File not found: $filePath");
		}
	}
}
