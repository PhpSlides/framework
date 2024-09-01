<?php declare(strict_types=1);

namespace PhpSlides\Interface;

interface ApplicationInterface
{
	/**
	 * Configure the application with the base path.
	 *
	 * @param string $basePath The base path of the application.
	 * @return self Returns an instance of the implementing class.
	 */
	public static function configure (string $basePath): self;

	/**
	 * Create the application by loading configuration files and routes.
	 *
	 * @return void
	 */
	public function create (): void;
}