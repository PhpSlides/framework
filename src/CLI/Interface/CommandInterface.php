<?php declare(strict_types=1);

namespace PhpSlides\CLI\Interface;

interface CommandInterface
{
	public static function showHelp (): void;

	public static function makeController (
	 array $arguments,
	 string $baseDir,
	): void;

	public static function makeApiController (
	 array $arguments,
	 string $baseDir,
	): void;

	public static function makeAuthGuard (
	 array $arguments,
	 string $baseDir,
	): void;

	public static function generateSecretKey (array $arguments): void;
}