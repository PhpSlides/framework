<?php declare(strict_types=1);

namespace PhpSlides\Console;

use PhpSlides\Console\Style\Console;
use PhpSlides\Console\Style\ColorCode;
use PhpSlides\Console\Interface\ServerInterface;

/**
 * PhpSlides Server
 *
 * This class creates a dynamic PHP server,
 * allowing for start, stop, and status commands.
 */
class Server implements ServerInterface
{
	private string $resolve;
	private string $host;
	private int $port;
	private int $pid;

	private $process = false;
	private $pipes = null;

	/**
	 * Check if the port is currently in use.
	 *
	 * @return bool True if the port is in use, false otherwise.
	 */
	public function isPortInUse (): bool
	{
		$connection = @fsockopen($this->host, $this->port);
		if (is_resource($connection))
		{
			fclose($connection);
			return true;
		}
		return false;
	}

	/**
	 * Start the PhpSlides server.
	 *
	 * @return bool True if the server started successfully, false otherwise.
	 */
	public function startServer (): bool
	{
		$descriptorspec = [
		 0 => [ 'pipe', 'r' ], // stdin is a pipe that the child will read from
		 1 => [ 'pipe', 'w' ], // stdout is a pipe that the child will write to
		 2 => [ 'pipe', 'w' ] // stderr is a pipe that the child will write to
		];

		$command = sprintf(
		 'php -S %s:%s -t %s %s',
		 $this->host,
		 $this->port,
		 $this->resolve,
		 $this->resolve . 'app.php'
		);

		$this->process = proc_open($command, $descriptorspec, $pipes);
		$this->pipes = $pipes;

		if (is_resource($this->process))
		{
			$this->pid = proc_get_status($this->process)['pid'];

			echo Console::bold(
			 "Started server at http://{$this->host}:{$this->port}\n"
			);
			echo Console::text(
			 "Type 'stop' or Ctrl+C to stop the server.",
			 ColorCode::BG_CYAN,
			 ColorCode::BOLD
			);
			echo "\n\n";

			return true;
		}
		else
		{
			return false;
		}
	}

	/**
	 * Stop the PHP server.
	 */
	public function stopServer (): void
	{
		if (is_resource($this->process))
		{
			proc_terminate($this->process);
			proc_close($this->process);
			echo "Server stopped.\n";
		}
	}

	/**
	 * Display the current status of the server.
	 */
	public function serverStatus (): void
	{
		$status = proc_get_status($this->process);

		if ($status['running'])
		{
			echo "Server running with PID {$status['pid']}\n";
		}
		else
		{
			echo "Server is not running.\n";
		}
	}

	/**
	 * Display available server commands.
	 */
	public function showCommands (): void
	{
		echo "stop    - Stop the server\n";
		echo "status  - Display server status\n";
		echo "restart - Restart the server\n";
		echo "help    - Display this help message\n\n";
	}

	/**
	 * Server constructor.
	 *
	 * @param array $addr The address and port to bind the server to.
	 * @param bool $is_debug Enable or disable debug mode.
	 * @param string $resolve The directory to serve files from.
	 */
	public function __construct (array $addr, bool $is_debug, string $resolve)
	{
		$this->host = $addr[0];
		$this->port = $addr[1];
		$this->resolve = $resolve . '/src/bootstrap/';

		// Check if the provided local address and its port is in use.
		if ($this->isPortInUse())
		{
			echo Console::bgRed('Error: ');
			echo Console::bold(" Port {$this->port} is already in use.\n");
			exit(1);
		}

		// Start the server, and check if the server started successfully.
		if (!$this->startServer())
		{
			exit(
			 Console::text(
			  "Error while starting server.\n",
			  ColorCode::BG_RED,
			  ColorCode::BOLD
			 )
			);
		}
		$this->showCommands();

		// Register the stopServer function to be called on script termination
		register_shutdown_function(function ()
		{
			$this->stopServer();
		}, $this->process);

		while (true)
		{
			$input = fgets(STDIN); // Using STDIN directly

			if (trim($input) === 'stop')
			{
				$this->stopServer();
				break;
			}

			if ($input !== false)
			{
				$input = trim($input);
				$this->handleInputCommands($input);
			}

			sleep(1);
		}

		exit();
	}

	/**
	 * Handle input commands from the giving input.
	 *
	 * @param string $input The input string.
	 */
	public function handleInputCommands (string $input): void
	{
		switch ($input)
		{
			case 'status':
				$this->serverStatus();
				break;

			case 'restart':
				$this->stopServer();
				$this->startServer();
				break;

			case 'help':
				echo "\nAvailable commands:\n";
				$this->showCommands();
				break;

			default:
				echo "Unknown command: $input\n";
				echo "Type 'help' for a list of available commands.\n";
				break;
		}
	}
}