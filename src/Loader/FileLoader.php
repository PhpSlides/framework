<?php

namespace PhpSlides\Core\Loader;

use PhpSlides\Exception;

/**
 * The FileLoader class provides functionality for loading, parsing, and
 * retrieving PHP files dynamically. It includes options for loading files
 * safely (skipping non-existent files) and parsing file contents as strings.
 */
class FileLoader
{
	/**
	 * Stores the result of each loaded or parsed file.
	 * @var array|null $result
	 */
	private array|null $result = null;

	/**
	 * Loads a specified PHP file into the project.
	 *
	 * This method attempts to include the given file. If successful, the result
	 * is stored and the method returns the current instance for method chaining.
	 *
	 * @param string $file The file path to load.
	 * @throws Exception if the specified file does not exist.
	 * @return self The instance for chaining.
	 */
	public function load ($file): self
	{
		if (file_exists($file))
		{
			$result = include $file;
			$this->result[] = $result;
			return $this;
		}
		else
		{
			throw new Exception("File does not exist: $file");
		}
	}

	/**
	 * Safely loads a specified PHP file into the project.
	 *
	 * This method attempts to include the specified file only if it exists,
	 * storing the result without throwing an exception if the file is missing.
	 *
	 * @param string $file The file path to load.
	 * @return self The instance for chaining.
	 */
	public function safeLoad ($file): self
	{
		if (file_exists($file))
		{
			$result = include $file;
			$this->result[] = $result;
		}
		return $this;
	}

	/**
	 * Retrieves the result of loaded or parsed files.
	 *
	 * If only one file has been loaded, this method returns its content directly.
	 * Otherwise, it returns an array of results for multiple files.
	 *
	 * @return mixed The content of the loaded file(s), either as a single result or an array.
	 */
	public function getLoad ()
	{
		if (count($this->result ?? []) === 1)
		{
			return $this->result[0];
		}
		return $this->result;
	}

	/**
	 * Parses a specified PHP file and stores its content as a string.
	 *
	 * This method includes the file, capturing its output without executing
	 * any code within it. Parsed content is stored, with empty content represented
	 * as an empty string.
	 *
	 * @param string $file The file path to parse.
	 * @throws Exception if the specified file does not exist.
	 * @return self The instance for chaining.
	 */
	public function parseLoad (string $file): self
	{
		if (file_exists($file))
		{
			ob_start();
			include $file;
			$output = ob_get_clean();
			$this->result[] =
			 $output !== false && strlen($output ?? '') > 0 ? $output : '';
			return $this;
		}
		else
		{
			throw new Exception("File does not exist: $file");
		}
	}

	/**
	 * Safely parses a specified PHP file and stores its content as a string.
	 *
	 * This method captures the file’s output without executing code in the file.
	 * If the file is missing, no exception is thrown, allowing flexible handling.
	 *
	 * @param string $file The file path to parse.
	 * @return self The instance for chaining.
	 */
	public function parseSafeLoad (string $file): self
	{
		if (file_exists($file))
		{
			ob_start();
			include $file;
			$output = ob_get_clean();
			$this->result[] =
			 $output !== false && strlen($output ?? '') > 0 ? $output : '';
		}
		return $this;
	}
}
