<?php
/**
 * @format
 */

namespace PhpSlides\Formatter\Views;

use PhpSlides\Foundation\Application;

/**
 * Trait to add Hot Reload functionality in PhpSlides view files.
 *
 * This trait inserts a hot reload script into the view file. If the `HOT_RELOAD`
 * environment variable is set to 'true', a JavaScript script is added to the
 * page that will check for file changes every second and reload the page if necessary.
 */
trait FormatHotReload
{
	/**
	 * Constructor.
	 *
	 * This constructor is a placeholder for any necessary initialization for
	 * the class using this trait. It currently does not perform any operations.
	 */
	public function __construct()
	{
		// code...
	}

	/**
	 * Adds Hot Reload functionality to the view file.
	 *
	 * This method checks if the `HOT_RELOAD` environment variable is set to 'true'.
	 * If so, it inserts a JavaScript snippet into the page that periodically checks
	 * the server for changes. If the server responds with 'reload', the page will
	 * reload automatically.
	 *
	 * The script also includes PhpSlides version and author information in the comment block.
	 */
	protected function hot_reload()
	{
		// Determine the protocol (http or https)
		$protocol =
			(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
			$_SERVER['SERVER_PORT'] == 443
				? 'https://'
				: 'http://';

		// Get session ID and PhpSlides version
		$sid = session_id();
		$phpslides_version = Application::PHPSLIDES_VERSION;
		$host = $protocol . $_SERVER['HTTP_HOST'] . "/hot-reload-a$sid";

		// Check if HOT_RELOAD is enabled in the environment
		if (getenv('HOT_RELOAD') == 'true') {
			// Insert hot reload script into the contents
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
				$this->contents,
			);
		}

		// Update the contents with the hot reload script if enabled
		$this->contents = $formattedContents;
	}
}
