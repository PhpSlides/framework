<?php

namespace PhpSlides\Loader;

use PhpSlides\Exception;

class FileLoader
{
	private array|null $result = null;

	/**
	 * Load File, and include it in your project
	 *
	 * @throws Exception if the file does not seem to be existing
	 * @return self
	 */
	public function load ($file): self
	{
		/**
		 * Checks if File exists
		 */
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
	 * Load File, and include it in your project.
	 * If the file does not exist then nothing will be executed.
	 *
	 * @return self
	 */
	public function safeLoad ($file): self
	{
		/**
		 * Checks if File exists
		 */
		if (file_exists($file))
		{
			$result = include $file;
			$this->result[] = $result;
		}
		return $this;
	}

	/**
	 * Get Loaded File Result
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
	 * Load File Contents and return Parsed Content
	 * Loaded File will be loaded without executing any code in the file.
	 *
	 * It'll wrap the whole code and return them
	 * Load Included Contents as String
	 *
	 * @return self Parsed File content as `string` and if no content, returns empty `string`
	 */
	public function parseLoad (string $file): self
	{
		/**
		 * Checks if File exists
		 */
		if (file_exists($file))
		{
			/**
			 * Store the file content and clear cache
			 */
			ob_start();
			include $file;
			$output = ob_get_clean();

			if ($output !== false && strlen($output ?? '') > 0)
			{
				$this->result[] = $output;
			}
			else
			{
				$this->result[] = '';
			}
			return $this;
		}
		else
		{
			throw new Exception("File does not exist: $file");
		}
	}

	/**
	 * Load File Contents and return Parsed Content
	 * Loaded File will be loaded without executing any code in the file.
	 *
	 * It'll wrap the whole code and return them
	 * Load Included Contents as String.
	 * If the file does not exist then nothing will be executed.
	 *
	 * @throws An Exception if the file does not seem to be existing
	 * @return self Parsed File content as `string` and if no content, returns empty `string`
	 */
	public function parseSafeLoad (string $file): self
	{
		/**
		 * Checks if File exists
		 */
		if (file_exists($file))
		{
			/**
			 * Store the file content and clear cache
			 */
			ob_start();
			include $file;
			$output = ob_get_clean();

			if ($output !== false && strlen($output ?? '') > 0)
			{
				$this->result[] = $output;
			}
			else
			{
				$this->result[] = '';
			}
		}
		return $this;
	}
}