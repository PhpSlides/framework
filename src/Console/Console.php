<?php

namespace PhpSlides\Console;

class Console extends Command
{
	public function __construct($args)
	{
		exit(print_r($args));
		# Check for the command and arguments
		$command = $args[1] ?? '';
		$args = array_slice($args, 2);

		# Handle commands
		switch ($command) {
			case 'controller':
				echo "Creating controller...\n";
				sleep(1);

				$cn = $args[0];
				$ct = $args[1] ?? null;
				$allTypes = ['-s', '--strict'];

				if ($ct !== null && !in_array($ct, $allTypes)) {
					exit("\033[31mInvalid Arguments: $ct\033[0m\n");
				}

				self::createController($cn, $ct);
				echo "\n\033[32mCreated controller successfully at \033[4m`controller/$cn.php`\033[0m";
				break;

			case 'api-controller':
				echo "Creating api controller...\n";
				sleep(1);

				$cn = $args[0];
				$ct = $args[1] ?? null;
				$allTypes = ['-s', '--strict'];

				if ($ct !== null && !in_array($ct, $allTypes)) {
					exit("\033[31mInvalid Arguments: $ct\033[0m\n");
				}

				self::createApiController($cn, $ct ?? null);
				echo "\n\033[32mCreated api controller successfully at \033[4m`controller/api/$cn.php`\033[0m";
				break;

			case 'middleware':
				echo "Creating middleware...\n";
				sleep(1);

				$cn = $args[0];
				$ct = $args[1] ?? null;
				$allTypes = ['-s', '--strict'];

				if ($ct !== null && !in_array($ct, $allTypes)) {
					exit("\033[31mInvalid Arguments: $ct\033[0m\n");
				}

				self::createMiddleware($cn, $ct ?? null);
				echo "\n\033[32mCreated middleware successfully at \033[4m`middleware/$cn.php`\033[0m";
				break;

			case '--help':
				echo self::showHelp();
				break;
			case '-h':
				echo self::showHelp();
				break;
			case null:
				echo self::showHelp();
				break;
			default:
				echo "\033[31mCommand not recognized.\033[0m\nFor list of commands run php create --help";
				break;
		}
	}

	public function output()
	{
		return self::finally();
	}
}
