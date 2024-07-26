<?php declare(strict_types=1);

namespace PhpSlides\Console\Interface;

interface StyleConsoleInterface
{
   public function text(string $message, int $code): string;
}