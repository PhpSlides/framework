<?php declare(strict_types=1);

namespace PhpSlides\Loader;

use PhpSlides\Exception;
use PhpSlides\Formatter\ViewFormatter;

class ViewLoader
{
	private array|null $result = null;

	/**
	 * Load view file in view formatted way
	 *
	 * @param string $viewFile The file path to be loaded
	 * @param mixed ...$props Properties in which would be available in the file
	 * @throws Exception if the file does not seem to be existing
	 * @return self
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
	 * Load view file in view formatted way.
	 * If the file does not exist then nothing will be executed.
	 *
	 * @param string $viewFile The file path to be loaded
	 * @param mixed ...$props Properties in which would be available in the file
	 * @return self
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
			// get and make generated file name & directory
			$gen_file = explode('/', $viewFile);
			$new_name = explode('.', end($gen_file), 2);
			$new_name = $new_name[0] . '.g.' . $new_name[1];

			$gen_file[count($gen_file) - 1] = $new_name;
			$gen_file = implode('/', $gen_file);

			$file_contents = file_get_contents($viewFile);
			$file_contents = $this->format($file_contents, ...$props);

			try {
				$file = fopen($gen_file, 'w');
				fwrite($file, $file_contents);
				fclose($file);

				$parsedLoad = (new FileLoader())->parseLoad($gen_file);
				$this->result[] = $parsedLoad->getLoad();

				unset($GLOBALS['__gen_file_path']);
			} finally {
				unlink($gen_file);
				$GLOBALS['__gen_file_path'] = $viewFile;
			}
		}
		return $this;
	}

	/**
	 * Get Loaded View File Result
	 */
	public function getLoad()
	{
		if (count($this->result ?? []) === 1) {
			return $this->result[0];
		}
		return $this->result;
	}

	private function format(string $contents, mixed ...$props)
	{
		return (new ViewFormatter($contents))->resolve(...$props);
	}
}
