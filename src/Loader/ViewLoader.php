<?php

namespace PhpSlides\Loader;

use PhpSlides\Exception;

class ViewLoader
{
	private array $result = [];

	public function load ($viewFile): self
	{
		if (is_file($viewFile))
		{
			// get and make generated file name & directory
			$gen_file = explode('/', $viewFile);
			$new_name = explode('.', end($gen_file), 2);
			$new_name = ucfirst($new_name[0]) . '.g.' . $new_name[1];

			$gen_file[count($gen_file) - 1] = $new_name;
			$gen_file = implode('/', $gen_file);

			$file_contents = file_get_contents($viewFile);
			$file_contents = $this->format($file_contents);

			try
			{
				$file = fopen($gen_file, 'w');
				fwrite($file, $file_contents);
				fclose($file);

				$parsedLoad = (new FileLoader())->parseLoad($gen_file);
				$this->result[] = $parsedLoad->getLoad();

				return $this;
			}
			finally
			{
				unlink($gen_file);
			}
		}
		else
		{
			throw new Exception("File not found: $viewFile");
		}
	}

	/**
	 * Get Loaded View File Result
	 */
	public function getLoad ()
	{
		if (count($this->result) === 1)
		{
			return $this->result[0];
		}
		return $this->result;
	}

	protected function format ($contents)
	{
		$pattern = '/<include\s+path=["|\']([^"]+)["|\']\s*!?\s*\/>/';

		// replace <include> match elements
		$formattedContents = preg_replace_callback(
		 $pattern,
		 function ($matches)
		 {
			 $path = trim($matches[1]);
			 return '<' . '?' . ' slides_include(__DIR__ . \'/' . $path . '\') ?' . '>';
		 },
		 $contents
		);

		// replace <? elements
		$formattedContents = preg_replace_callback(
		 '/<' . '\?' . ' ([^?]*)\?' . '>/s',
		 function ($matches)
		 {
			 $val = trim($matches[1]);
			 $val = trim($val, ';');
			 return '<' . '?php print_r(' . $val . ') ?>';
		 },
		$formattedContents
		);

		return $formattedContents;
	}
}