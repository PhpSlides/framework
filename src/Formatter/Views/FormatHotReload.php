<?php

namespace PhpSlides\Formatter\Views;

use PhpSlides\Foundation\Application;

/**
 *
 */
trait FormatHotReload
{
	/**
	 *
	 */
	public function __construct()
	{
		// code...
	}

	/**
	 * Add PhpSlides Hot reload to view file
	 */
	protected function hot_reload()
	{
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
       *
       * @version $phpslides_version
       * @author Dave Conco <info@dconco.dev>
       * @copyright 2023 - 2024 Dave Conco
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
				$this->contents
			);
		}

		$this->contents = $formattedContents;
	}
}
