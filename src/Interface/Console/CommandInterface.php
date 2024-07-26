<?php

namespace PhpSlides\Interface\Console;

interface CommandInterface
{
	public static function showHelp(): void;
   
	public static function createController($cn, $ct): void;

	public static function createApiController($cn, $ct): void;
   

}