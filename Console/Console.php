<?php

namespace PhpSlides\Console;

use PhpSlides\Console\Style\ColorCode;
use PhpSlides\Console\Interface\CommandInterface;
use PhpSlides\Console\Interface\ConsoleInterface;
use PhpSlides\Console\Style\Console as StyleConsole;

class Console extends Command implements CommandInterface, ConsoleInterface
{
	public function __construct (array $argv)
	{
		# Check for the command and arguments
		$command = $argv;
		array_shift($command);

		$arguments = array_slice($command, 1);
		$options = getopt('h', [ 'help' ]);

		if (isset($options['help']))
			self::showHelp();

		print_r($arguments);

		# Handle commands
		switch ($command)
		{
			default:
				$colors = [ ColorCode::RED ];

				echo StyleConsole::text('Invalid Command! Type --help for list of commands', ...$colors);
				break;
		}
	}

	public function output (): string
	{
		return "\n";
	}
}