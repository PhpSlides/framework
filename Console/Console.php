<?php declare(strict_types=1);

namespace PhpSlides;

use PhpSlides\CLI\Server;
use PhpSlides\CLI\Command;
use PhpSlides\CLI\Style\ColorCode;
use PhpSlides\CLI\Interface\CommandInterface;
use PhpSlides\CLI\Style\Console as StyleConsole;
use PhpSlides\Interface\ConsoleInterface;
use PhpSlides\Foundation\Application;

/**
 * PhpSlides Console
 *
 * This class handles command line input
 * and manages server-related commands.
 */
class Console extends Command implements CommandInterface, ConsoleInterface
{
	private static array $listen = [];
	private static bool $serve = false;
	private static bool $is_debug = false;
	private static string $resolve = 'src/bootstrap';
	private static ?array $commands = null;

	/**
	 * Console constructor.
	 *
	 * @param array $argv The command line arguments.
	 */
	public function __construct(array $argv)
	{
		# Check for the command and arguments
		$command = $argv;
		array_shift($command);

		$arguments = array_slice($command, 1);
		$options = getopt('h', ['help']);

		if (isset($options['help']) || isset($options['h'])) {
			self::showHelp();
		}

		# Handle commands
		switch ($command[0] ?? null) {
			case 'serve':
				self::$serve = true;
				break;

			case 'make:controller':
				if (count($arguments) < 1) {
					exit(
						"<name> argument is required! Type --help for list of commands\n"
					);
				}
				self::$commands = ['controller', $arguments];
				break;

			case 'make:api-controller':
				if (count($arguments) < 1) {
					exit(
						"<name> argument is required! Type --help for list of commands\n"
					);
				}
				self::$commands = ['api-controller', $arguments];
				break;

			case 'make:auth-guard':
				if (count($arguments) < 1) {
					exit(
						"<name> argument is required! Type --help for list of commands\n"
					);
				}
				self::$commands = ['auth-guard', $arguments];
				break;

			case 'generate:secret-key':
				self::$commands = ['secret-key', $arguments];
				break;

			default:
				$styles = [ColorCode::WHITE, ColorCode::BG_RED];

				echo StyleConsole::text(
					'Command not Recognized! See \'php slide --help\'',
					...$styles
				);
				break;
		}
	}

	/**
	 * Console destructor.
	 */
	public function __destruct()
	{
		if (self::$serve) {
			new Server(
				addr: self::$listen,
				is_debug: self::$is_debug,
				resolve: self::$resolve
			);
		}

		if (self::$commands) {
			switch (self::$commands[0]) {
				case 'controller':
					self::makeController(self::$commands[1], self::$resolve);
					break;
				case 'api-controller':
					self::makeApiController(self::$commands[1], self::$resolve);
					break;
				case 'auth-guard':
					self::makeAuthGuard(self::$commands[1], self::$resolve);
					break;
				case 'secret-key':
					self::generateSecretKey(self::$commands[1]);
					break;
				default:
					exit('Error.');
			}
		}

		echo "\n";
	}

	/**
	 * Resolve the path for the server.
	 *
	 * @param string $baseDir The project root directory path.
	 * @return self
	 */
	public function resolve(string $baseDir): self
	{
		self::$resolve = rtrim($baseDir, '/');
		return $this;
	}

	/**
	 * Set the host and port for the server to listen on.
	 *
	 * @param string $host The hostname.
	 * @param int $port The port number.
	 * @return self
	 */
	public function listen(string $host, int $port): self
	{
		self::$listen = [$host, $port];
		return $this;
	}

	/**
	 * Enable or disable debug mode.
	 *
	 * @param bool $is_debug True to enable debug mode, false to disable.
	 * @return self
	 */
	public function debug(bool $is_debug): self
	{
		self::$is_debug = $is_debug;
		return $this;
	}
}
