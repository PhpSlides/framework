<?php

namespace PhpSlides\Loader;

use Exception;

class FileLoader
{
	private array $result = [];

	/**
	 * Load File, and include it in your project
	 *
	 * @return self
	 */
	public function load($file): self
	{
		/**
		 * Checks if File exists
		 */
		if (file_exists($file)) {
			$result = include $file;
			$this->result[] = $result;

			return $this;
		} else {
			throw new Exception("File not found: $file");
		}
	}

	/**
	 * Get Loaded File Result
	 */
	public function getLoad()
	{
		if (count($this->result) === 1) {
			return $this->result[0];
		}
		return $this->result;
	}

	/**
	 * Load File Contents and return Parsed Content
	 * Load Included Contents as String
	 *
	 * @return self Parsed File content as `string` and if no content, returns empty `string`
	 */
	public function parseLoad(string $file): self
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
				$this->result[] = $output;
			} else {
				$this->result[] = '';
			}
			return $this;
		} else {
			throw new Exception("File not found: $file");
		}
	}
}
