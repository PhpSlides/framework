<?php declare(strict_types=1);

namespace PhpSlides\Console;

use PhpSlides\Http\Request;
use PhpSlides\Controller\Controller;
use PhpSlides\Console\Style\Console;
use PhpSlides\Http\Interface\ApiController;
use PhpSlides\Interface\MiddlewareInterface;
use PhpSlides\Console\Interface\CommandInterface;

class Command implements CommandInterface
{
	public static function showHelp(): void
	{
		echo file_get_contents(
			dirname(__DIR__) . '/Foundation/template/commands/Commands.md.dist'
		);
		exit();
	}

	public static function makeController(
		array $arguments,
		string $baseDir
	): void {
		$cn = $arguments[0];
		$ct = $arguments[1] ?? null;

		/**
		 * Converts controller class to CamelCase
		 * Adds Controller if its not specified
		 */
		$cn = strtoupper($cn[0]) . substr($cn, 1, strlen($cn));
		$cn = str_ends_with($cn, 'Controller') ? $cn : $cn . 'Controller';

		// create class name and namespace
		$namespace = 'App\\Controllers';
		$classname = $namespace . '\\' . $cn;

		$content = file_get_contents(
			dirname(__DIR__) .
				'/Foundation/template/controller/Controller.php.dist'
		);
		$strict = $ct === '--strict' ? 'declare(strict_types=1);' : '';

		$cc = Controller::class;
		$use = "use $cc;";

		$content = str_replace('{{name}}', $cn, $content);
		$content = str_replace(
			'<?php',
			"<?php $strict\n\nnamespace $namespace;\n\n$use",
			$content
		);

		// checks if class already exists
		if (class_exists($classname)) {
			echo Console::bgRed('Error: ');
			echo Console::bold(
				" File name already exists at app/Controllers/$cn.php\n"
			);
			// checks if controller file already exists
		} elseif (file_exists("$baseDir/app/Controllers/$cn.php")) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" Controller class already exists: $cn\n");
		}
		// if cannot add contents to the file
		elseif (
			!file_put_contents("$baseDir/app/Controllers/$cn.php", $content)
		) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" Unable to create controller: $cn\n");
		}
		// if controller is added successfully
		else {
			shell_exec('composer dump-autoload');
			echo Console::bold(
				"$cn created successfully at app/Controllers/$cn.php\n"
			);
		}

		exit();
	}

	public static function makeApiController(
		array $arguments,
		string $baseDir
	): void {
		$cn = $arguments[0];
		$ct = $arguments[1] ?? null;

		/**
		 * Converts controller class to CamelCase
		 * Adds Controller if its not specified
		 */
		$cn = strtoupper($cn[0]) . substr($cn, 1, strlen($cn));
		$cn = str_ends_with($cn, 'Controller') ? $cn : $cn . 'Controller';

		// create class name and namespace
		$namespace = 'App\\Controllers';
		$classname = $namespace . '\\' . $cn;

		$content = file_get_contents(
			dirname(__DIR__) . '/Foundation/template/api/ApiController.php.dist'
		);
		$strict = $ct === '--strict' ? 'declare(strict_types=1);' : '';

		$req = Request::class;
		$api_c = ApiController::class;
		$use = "use $req;\nuse $api_c;";

		$content = str_replace('{{name}}', $cn, $content);
		$content = str_replace(
			'<?php',
			"<?php $strict\n\nnamespace $namespace;\n\n$use",
			$content
		);

		// checks if class already exists
		if (class_exists($classname)) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" Controller class already exists: $cn\n");
		}
		// checks if controller file already exists
		elseif (file_exists("$baseDir/app/Controllers/api/$cn.php")) {
			echo Console::bgRed('Error: ');
			echo Console::bold(
				" File name already exists at app/Controllers/api/$cn.php\n"
			);
		}
		// if cannot add contents to the file
		elseif (
			!file_put_contents("$baseDir/app/Controllers/api/$cn.php", $content)
		) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" Unable to create api controller: $cn\n");
		}
		// if api controller is added successfully
		else {
			shell_exec('composer dump-autoload');
			echo Console::bold(
				"$cn created successfully at app/Controllers/api/$cn.php\n"
			);
		}

		exit();
	}

	public static function makeMiddleware(
		array $arguments,
		string $baseDir
	): void {
		$cn = $arguments[0];
		$ct = $arguments[1] ?? null;

		/**
		 * Converts middleware class to CamelCase
		 * Adds Middleware if its not specified
		 */
		$cn = strtoupper($cn[0]) . substr($cn, 1, strlen($cn));
		$cn = str_ends_with($cn, 'Middleware') ? $cn : $cn . 'Middleware';

		// create class name and namespace
		$namespace = 'App\\Middlewares';
		$classname = $namespace . '\\' . $cn;

		$content = file_get_contents(
			dirname(__DIR__) .
				'/Foundation/template/middleware/Middleware.php.dist'
		);
		$strict = $ct === '--strict' ? 'declare(strict_types=1);' : '';

		$req = Request::class;
		$mwc = MiddlewareInterface::class;
		$use = "use Closure;\nuse $req;\nuse $mwc;";

		$content = str_replace('{{name}}', $cn, $content);
		$content = str_replace(
			'<?php',
			"<?php $strict\n\nnamespace $namespace;\n\n$use",
			$content
		);

		// checks if class already exists
		if (class_exists($classname)) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" Middleware class already exists: $cn\n");
		}
		// checks if middleware file already exists
		elseif (file_exists("$baseDir/app/Middlewares/$cn.php")) {
			echo Console::bgRed('Error: ');
			echo Console::bold(
				" File name already exists at app/Middlewares/$cn.php\n"
			);
		}
		// if cannot add contents to the file
		elseif (
			!file_put_contents("$baseDir/app/Middlewares/$cn.php", $content)
		) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" Unable to create middleware: $cn\n");
		}
		// if middleware is added successfully
		else {
			shell_exec('composer dump-autoload');
			echo Console::bold(
				"$cn created successfully at app/Middlewares/$cn.php\n"
			);
		}

		exit();
	}
}
