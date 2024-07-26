<?php

namespace PhpSlides\Console;

use PhpSlides\Console\Interface\CommandInterface;
use PhpSlides\Console\Interface\ConsoleInterface;

class Console extends Command implements CommandInterface, ConsoleInterface
{
	public function __construct($argv)
	{
		# Check for the command and arguments
		$command = $argv[1] ?? '';
		$options = getopt('hs', ['help', 'strict']);

		# Handle commands
		switch ($command) {
			
		}
	}

	public function output(): string
	{
		return self::finally();
	}
}