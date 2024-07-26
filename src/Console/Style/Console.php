<?php declare(strict_types=1);

namespace PhpSlides\Console\Style;

use PhpSlides\Console\Interface\ColorCodeInterface;
use PhpSlides\Console\Interface\StyleConsoleInterface;

class Console extends ColorCode implements
	ColorCodeInterface,
	StyleConsoleInterface
{
	public function text(string $message, int $code): string
	{
		return parent::START . $code . 'm' . $message . parent::RESET;
	}
}
