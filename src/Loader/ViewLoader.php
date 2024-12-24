<?php declare(strict_types=1);

namespace PhpSlides\Src\Loader;

use PhpSlides\Exception;
use PhpSlides\Src\Formatter\ViewFormatter;

/**
 * The ViewLoader class dynamically loads and formats view files, applying
 * custom properties and returning the processed content. This class
 * supports loading different view file formats, generating and parsing
 * them as needed, and storing the resulting output.
 */
class ViewLoader
{
	/**
	 * Stores the result of each loaded or parsed view file.
	 * @var array|null $result
	 */
	private array|null $result = null;

	/**
	 * Loads a view file and applies view formatting.
	 *
	 * This method checks for the view file's existence in various formats
	 * (e.g., .psl, .view.php). If found, it loads and formats the content
	 * using the provided properties. Throws an exception if the file
	 * does not exist.
	 *
	 * @param string $viewFile The file path to be loaded.
	 * @param mixed ...$props Properties to be available in the view file.
	 * @throws Exception if the specified view file does not exist.
	 * @return self The instance for chaining.
	 */
	public function load(string $viewFile, mixed ...$props): self
	{
		if (
			!is_file($viewFile) &&
			!is_file($viewFile . '.psl') &&
			!is_file($viewFile . '.view.php')
		) {
			throw new Exception("File does not exist: $viewFile");
		}
		return self::safeLoad($viewFile, ...$props);
	}

	/**
	 * Safely loads a view file and applies view formatting.
	 *
	 * Similar to `load`, but if the view file is not found, it simply
	 * returns without throwing an exception. Supports loading files with
	 * .psl or .view.php extensions and applies formatting with provided
	 * properties.
	 *
	 * @param string $viewFile The file path to be loaded.
	 * @param mixed ...$props Properties to be available in the view file.
	 * @return self The instance for chaining.
	 */
	public function safeLoad(string $viewFile, mixed ...$props): self
	{
		if (is_file($viewFile . '.psl')) {
			$viewFile = $viewFile . '.psl';
		}
		if (is_file($viewFile . '.view.php')) {
			$viewFile = $viewFile . '.view.php';
		}
		if (is_file($viewFile)) {
			// Generate filename and directory for formatted file.
			$gen_file = explode('/', $viewFile);
			$new_name = explode('.', end($gen_file), 2);
			$new_name = $new_name[0] . '.g.' . $new_name[1];

			$gen_file[count($gen_file) - 1] = $new_name;
			$gen_file = implode('/', $gen_file);

			$file_contents = file_get_contents($viewFile);
			$file_contents = $this->format($file_contents, ...$props);

			try {
				// Write formatted contents to the generated file and parse it.
				$file = fopen($gen_file, 'w');
				fwrite($file, $file_contents);
				fclose($file);

				$parsedLoad = (new FileLoader())->parseLoad($gen_file);
				$this->result[] = $parsedLoad->getLoad();

				unset($GLOBALS['__gen_file_path']);
			} finally {
				// Remove generated file and reset global file path.
				unlink($gen_file);
				$GLOBALS['__gen_file_path'] = $viewFile;
			}
		}
		return $this;
	}

	/**
	 * Retrieves the result of the loaded or parsed view file.
	 *
	 * If a single view file has been loaded, this method returns its content
	 * directly. For multiple loaded files, it returns an array of results.
	 *
	 * @return mixed The content of the loaded view file(s), either as a single
	 *               result or an array.
	 */
	public function getLoad()
	{
		if (count($this->result ?? []) === 1) {
			return $this->result[0];
		}
		return $this->result;
	}

	/**
	 * Formats view file content using the ViewFormatter.
	 *
	 * @param string $contents The view file content to format.
	 * @param mixed ...$props Properties to apply within the view file.
	 * @return string The formatted view file content.
	 */
	private function format(string $contents, mixed ...$props)
	{
		return (new ViewFormatter($contents))->resolve(...$props);
	}
}
