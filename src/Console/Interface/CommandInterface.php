<?php

namespace PhpSlides\Console\Interface;

interface CommandInterface
{
	public static function showHelp(): void;

	public static function createController($cn, $ct): void;

	public static function createApiController($cn, $ct): void;

	public static function createMiddleware($cn, $ct): void;
}