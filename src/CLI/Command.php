<?php declare(strict_types=1);

namespace PhpSlides\CLI;

use PhpSlides\Http\Request;
use PhpSlides\CLI\Style\Console;
use PhpSlides\Http\ApiController;
use PhpSlides\Http\Auth\AuthGuard;
use PhpSlides\Controller\Controller;
use PhpSlides\CLI\Interface\CommandInterface;

class Command implements CommandInterface
{
	/**
	 * SHOW HELP MESSAGE IN THE CONSOLE
	 * --help command
	 */
	public static function showHelp (): void
	{
		echo Console::yellow("______  _           _____ _       _\n");
		usleep(100000);
		echo Console::yellow("|  __ \| |         / ____| (•)   | | \n");
		usleep(100000);
		echo Console::yellow("| |__) | |__  _ __| (___ | |_  __| | ___\n");
		usleep(100000);
		echo Console::yellow("|  ___/| '_ \| '_ \\\\___ \| | |/ _` |/ _ \\\n");
		usleep(100000);
		echo Console::yellow("| |    | | | | |_) |___) | | | (_| |  __/\n");
		usleep(100000);
		echo Console::yellow("|_|    |_| |_| .__/_____/|_|_|\__,_|\___|\n");
		usleep(100000);
		echo Console::yellow("             | |\n");
		usleep(100000);
		echo Console::yellow("             |_|\n\n");
		usleep(100000);
		echo Console::yellow('Usage:');
		echo " php slide [command] [options] [...args]\n\n";
		usleep(100000);
		echo Console::yellow("Commands:\n");
		usleep(100000);
		echo Console::green('  serve');
		echo "                        Run and serve the PhpSlides project in dev mode.\n";
		usleep(100000);
		echo Console::green('  make:controller <name>');
		echo "       Create a route controller class.\n";
		usleep(100000);
		echo Console::green('  make:api-controller <name>');
		echo "   Create an API controller class.\n";
		usleep(100000);
		echo Console::green('  make:auth-guard <name>');
		echo "       Create an AuthGuard class.\n";
		usleep(100000);
		echo Console::green('  make:forge-db [db] <tables>');
		echo "  Create new Tables & Columns in a Forge Database.\n";
		usleep(100000);
		echo Console::green('  generate:secret-key <length>');
		echo " Generate a random secret key for JWT.\n";
		usleep(100000);
		echo Console::green('  -h, --help');
		echo "                   Show this help message and exit.\n\n";
		usleep(100000);
		echo Console::yellow("Options:\n");
		usleep(100000);
		echo Console::green('   --strict');
		echo "                    Enforce strict class standards in the code.\n\n";

		/*echo file_get_contents(
																 dirname(__DIR__) . '/Foundation/template/commands/Commands.tmp'
															 );*/
		exit();
	}

	/**
	 * MAKE CONTROLLER CLASS AND ADD FILES IN THE CONTROLLER LOCATION
	 * make:controller command
	 *
	 * @param array $arguments It contains details of the database to create
	 * @param string $baseDir This is required for where to create the database
	 */
	public static function makeController (
	 array $arguments,
	 string $baseDir,
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
		 dirname(__DIR__) . '/Foundation/template/controller/Controller.tmp'
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
		if (class_exists($classname))
		{
			echo Console::bgRed('Error: ');
			echo Console::bold(
			 " File name already exists at app/Controller/$cn.php\n"
			);
			// checks if controller file already exists
		}
		elseif (file_exists("$baseDir/app/Controller/$cn.php"))
		{
			echo Console::bgRed('Error: ');
			echo Console::bold(" Controller class already exists: $cn\n");
		}
		// if cannot add contents to the file
		elseif (!file_put_contents("$baseDir/app/Controller/$cn.php", $content))
		{
			echo Console::bgRed('Error: ');
			echo Console::bold(" Unable to create controller: $cn\n");
		}
		// if controller is added successfully
		else
		{
			shell_exec('composer dump-autoload');
			echo Console::bold(
			 "$cn created successfully at app/Controller/$cn.php\n"
			);
		}

		exit();
	}

	/**
	 * MAKE API CONTROLLER CLASS
	 *
	 * @param array $arguments It contains details of the database to create
	 * @param string $baseDir This is required for where to create the database
	 */
	public static function makeApiController (
	 array $arguments,
	 string $baseDir,
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
		 dirname(__DIR__) . '/Foundation/template/api/ApiController.tmp'
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
		if (class_exists($classname))
		{
			echo Console::bgRed('Error: ');
			echo Console::bold(" Controller class already exists: $cn\n");
		}
		// checks if controller file already exists
		elseif (file_exists("$baseDir/app/Controller/Api/$cn.php"))
		{
			echo Console::bgRed('Error: ');
			echo Console::bold(
			 " File name already exists at app/Controller/Api/$cn.php\n"
			);
		}
		// if cannot add contents to the file
		elseif (
		!file_put_contents("$baseDir/app/Controller/Api/$cn.php", $content)
		)
		{
			echo Console::bgRed('Error: ');
			echo Console::bold(" Unable to create Api controller: $cn\n");
		}
		// if api controller is added successfully
		else
		{
			shell_exec('composer dump-autoload');
			echo Console::bold(
			 "$cn created successfully at app/Controller/Api/$cn.php\n"
			);
		}

		exit();
	}

	/**
	 * MAKE AUTHENTICATION GUARD FOR ROUTES
	 * make:auth-guard command
	 *
	 * @param array $arguments It contains details of the database to create
	 * @param string $baseDir This is required for where to create the database
	 */
	public static function makeAuthGuard (array $arguments, string $baseDir): void
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
		 dirname(__DIR__) . '/Foundation/template/guards/AuthGuard.tmp'
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
		if (class_exists($classname))
		{
			echo Console::bgRed('Error: ');
			echo Console::bold(" AuthGuard class already exists: $cn\n");
		}
		// checks if middleware file already exists
		elseif (file_exists("$baseDir/app/Guards/$cn.php"))
		{
			echo Console::bgRed('Error: ');
			echo Console::bold(
			 " File name already exists at app/Guards/$cn.php\n"
			);
		}
		// if cannot add contents to the file
		elseif (!file_put_contents("$baseDir/app/Guards/$cn.php", $content))
		{
			echo Console::bgRed('Error: ');
			echo Console::bold(" Unable to create AuthGuard: $cn\n");
		}
		// if middleware is added successfully
		else
		{
			shell_exec('composer dump-autoload');
			echo Console::bold("$cn created successfully at app/Guards/$cn.php\n");
		}

		exit();
	}

	/**
	 * GENERATE SECRET KEY FOR JWT USE
	 * generate:secret-key command
	 *
	 * @param array $arguments It contains details of the database to create
	 */
	public static function generateSecretKey (array $arguments): void
	{
		$length = $arguments[0] ?? 32;
		$key = base64_encode(random_bytes((int) $length));

		echo Console::bold("\n$key\n");
		exit();
	}

	/**
	 * CREATE A DATABASE USING THE FORGE COMMAND
	 * make:forge-db command
	 *
	 * @param array $arguments It contains details of the database to create
	 * @param string $baseDir This is required for where to create the database (not used)
	 */
	public static function makeForgeDB (array $arguments, string $baseDir): void
	{
		$db_name = $arguments[0];
		$table_name = $arguments[1] ?? null;
		$column_name = $arguments[2] ?? null;

		if (!$table_name)
		{
			exit("<table> option is required!\n");
		}

		# If there's no database directory, create it.
		if (!is_dir("app/Forge/$db_name"))
		{
			echo Console::green("Creating Database…\n");
			mkdir("app/Forge/$db_name");

			copy(
			 __DIR__ . '/../Database/options.sql',
			 "app/Forge/$db_name/options.sql"
			);
		}
		else
		{
			echo Console::yellow(
			 "`$db_name` database already exists, skipping…\n\n"
			);
		}
		usleep(300000);

		$dir = "app/Forge/$db_name/$table_name";

		# Checks if the table already exists, else create it
		if (is_dir($dir))
		{
			echo Console::yellow(
			 "`$table_name` table already exists, skipping…\n\n"
			);
		}
		else
		{
			echo Console::green("Creating Table…\n");
			mkdir($dir);
		}
		usleep(300000);
		echo Console::green("Adding columns…\n");

		/**
		 * Get content from the template Forge file
		 */
		$content = file_get_contents(
		 dirname(__DIR__) . '/Foundation/template/database/Forge.tmp'
		);
		$content = str_replace('{table_name}', $table_name, $content);
		$content = str_replace('{db_name}', $db_name, $content);

		if (!file_exists($dir . "/$table_name.php"))
		{
			file_put_contents($dir . "/$table_name.php", $content);
		}

		/**
		 * Checks if columns arguments are provided
		 */
		if ($column_name)
		{
			$total_columns = 0;
			$columns = mb_split(' ', $column_name);

			foreach ($columns as $key => $value)
			{
				$key += 1;
				$file = "$dir/$key-$value";
				usleep(300000);

				if (is_file($file))
				{
					echo Console::yellow(
					 "`$value` column already exists, skipping…\n"
					);
				}
				else
				{
					if ($value == 'id' || str_ends_with('_id', $value))
					{
						file_put_contents(
						 $file,
						 "TYPE => INT\nLENGTH => 11\nNULL => FALSE\nPRIMARY => TRUE\nAUTO_INCREMENT => TRUE"
						);
					}
					else
					{
						file_put_contents(
						 $file,
						 "TYPE => VARCHAR\nLENGTH => 225\nNULL => FALSE"
						);
					}
					$total_columns++;
				}
			}

			echo Console::green("Created table with $total_columns");
			echo Console::green($total_columns > 1 ? " columns.\n" : " column.\n");
		}
		else
		{
			echo Console::green("Created an empty table.\n");
		}

		usleep(300000);
		echo Console::green("Done ✅ \n");
	}
}
