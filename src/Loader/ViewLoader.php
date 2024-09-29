<?php

namespace PhpSlides\Loader;

use PhpSlides\Exception;
use PhpSlides\Foundation\Application;

class ViewLoader
{
	private array|null $result = null;

	/**
	 * Load view file in view formatted way
	 *
	 * @throws Exception if the file does not seem to be existing
	 * @return self
	 */
	public function load($viewFile): self
	{
		if (!is_file($viewFile)) {
			throw new Exception("File does not exist: $viewFile");
		}
		return self::safeLoad($viewFile);
	}

	/**
	 * Load view file in view formatted way.
	 * If the file does not exist then nothing will be executed.
	 *
	 * @return self
	 */
	public function safeLoad($viewFile): self
	{
		if (is_file($viewFile)) {
			// get and make generated file name & directory
			$gen_file = explode('/', $viewFile);
			$new_name = explode('.', end($gen_file), 2);
			$new_name = ucfirst($new_name[0]) . '.g.' . $new_name[1];

			$gen_file[count($gen_file) - 1] = $new_name;
			$gen_file = implode('/', $gen_file);

			$file_contents = file_get_contents($viewFile);
			$file_contents = $this->format($file_contents);

			try {
				$file = fopen($gen_file, 'w');
				fwrite($file, $file_contents);
				fclose($file);

				$parsedLoad = (new FileLoader())->parseLoad($gen_file);
				$this->result[] = $parsedLoad->getLoad();

				unlink($gen_file);
				unset($GLOBALS['__gen_file_path']);
			} finally {
				$GLOBALS['__gen_file_path'] = $gen_file;
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

	protected function format($contents)
	{
		$pattern = '/<include\s+path=["|\']([^"]+)["|\']\s*!?\s*\/>/';

		// replace <include> match elements
		$formattedContents = preg_replace_callback(
			$pattern,
			function ($matches) {
				$path = trim($matches[1]);
				return '<' .
					'? slides_include(__DIR__ . \'/' .
					$path .
					'\') ?' .
					'>';
			},
			$contents
		);

		// Replace bracket interpolation {{ }}
		$formattedContents = preg_replace_callback(
			'/{{\s*(.*?)\s*}}/',
			function ($matches) {
				return '"<' . '?php print_r(' . $matches[1] . ') ?' . '>"';
			},
			$formattedContents
		);

		// replace <? elements
		$formattedContents = preg_replace_callback(
			'/<' . '\?' . '\s+([^?]*)\?' . '>/s',
			function ($matches) {
				$val = trim($matches[1]);
				$val = trim($val, ';');
				return '<' . '?php print_r(' . $val . ') ?' . '>';
			},
			$formattedContents
		);

		$protocol =
			(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
			$_SERVER['SERVER_PORT'] == 443
				? 'https://'
				: 'http://';

		$sid = session_id();
		$phpslides_version = Application::PHPSLIDES_VERSION;
		$host = $protocol . $_SERVER['HTTP_HOST'] . "/hot-reload-$sid";

		if (getenv('HOT_RELOAD') == 'true') {
			$formattedContents = str_replace(
				'</body>',
				"\n
   <script>
      /**
       * PHPSLIDES HOT RELOAD GENERATED
       * @version $phpslides_version
       */
       setInterval(function() {
           fetch('$host', { method: 'POST' })
               .then(response => response.text())
               .then(data => {
                   if (data === 'reload') {
                       window.location.reload()
                   }
               });
       }, 1000);
   </script>\n
</body>",
				$formattedContents
			);
		}

		return $formattedContents;
	}
}
