<?php declare(strict_types=1);

namespace PhpSlides\CLI;

use PhpSlides\Http\Request;
use PhpSlides\Http\ApiController;
use PhpSlides\Http\Auth\AuthGuard;
use PhpSlides\Controller\Controller;
use PhpSlides\CLI\Style\Console;
use PhpSlides\CLI\Interface\CommandInterface;

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
		$namespace = 'App\\Controller';
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
				" File name already exists at app/Controller/$cn.php\n"
			);
			// checks if controller file already exists
		} elseif (file_exists("$baseDir/app/Controller/$cn.php")) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" Controller class already exists: $cn\n");
		}
		// if cannot add contents to the file
		elseif (!file_put_contents("$baseDir/app/Controller/$cn.php", $content)) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" Unable to create controller: $cn\n");
		}
		// if controller is added successfully
		else {
			shell_exec('composer dump-autoload');
			echo Console::bold(
				"$cn created successfully at app/Controller/$cn.php\n"
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
		$namespace = 'App\\Controller\\Api';
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
		elseif (file_exists("$baseDir/app/Controller/Api/$cn.php")) {
			echo Console::bgRed('Error: ');
			echo Console::bold(
				" File name already exists at app/Controller/Api/$cn.php\n"
			);
		}
		// if cannot add contents to the file
		elseif (
			!file_put_contents("$baseDir/app/Controller/Api/$cn.php", $content)
		) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" Unable to create Api controller: $cn\n");
		}
		// if api controller is added successfully
		else {
			shell_exec('composer dump-autoload');
			echo Console::bold(
				"$cn created successfully at app/Controller/Api/$cn.php\n"
			);
		}

		exit();
	}

	public static function makeAuthGuard(array $arguments, string $baseDir): void
	{
		$cn = $arguments[0];
		$ct = $arguments[1] ?? null;

		/**
		 * Converts middleware class to CamelCase
		 * Adds Middleware if its not specified
		 */
		$cn = strtoupper($cn[0]) . substr($cn, 1, strlen($cn));
		$cn = str_ends_with($cn, 'Guard') ? $cn : $cn . 'Guard';

		// create class name and namespace
		$namespace = 'App\\Guards';
		$classname = $namespace . '\\' . $cn;

		$content = file_get_contents(
			dirname(__DIR__) . '/Foundation/template/guards/AuthGuard.php.dist'
		);
		$strict = $ct === '--strict' ? 'declare(strict_types=1);' : '';

		$auth = AuthGuard::class;
		$use = "use $auth;";

		$content = str_replace('{{name}}', $cn, $content);
		$content = str_replace(
			'<?php',
			"<?php $strict\n\nnamespace $namespace;\n\n$use",
			$content
		);

		// checks if class already exists
		if (class_exists($classname)) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" AuthGuard class already exists: $cn\n");
		}
		// checks if middleware file already exists
		elseif (file_exists("$baseDir/app/Guards/$cn.php")) {
			echo Console::bgRed('Error: ');
			echo Console::bold(
				" File name already exists at app/Guards/$cn.php\n"
			);
		}
		// if cannot add contents to the file
		elseif (!file_put_contents("$baseDir/app/Guards/$cn.php", $content)) {
			echo Console::bgRed('Error: ');
			echo Console::bold(" Unable to create AuthGuard: $cn\n");
		}
		// if middleware is added successfully
		else {
			shell_exec('composer dump-autoload');
			echo Console::bold("$cn created successfully at app/Guards/$cn.php\n");
		}

		exit();
	}

	public static function generateSecretKey(array $arguments): void
	{
		$length = $arguments[0] ?? 32;
		$key = base64_encode(random_bytes((int) $length));

		echo Console::bold("\n$key\n");
		exit();
	}
}
