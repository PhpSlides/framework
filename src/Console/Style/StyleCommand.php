<?php declare(strict_types=1);

namespace PhpSlides\Console;

use PhpSlides\Console\Interface\ColorCodeInterface;
use PhpSlides\Console\Interface\StyleConsoleInterface;

class StyleConsole extends ColorCode implements
	ColorCodeInterface,
	StyleConsoleInterface
{
	public function text(string $message, int $code): string
	{
	}
}
