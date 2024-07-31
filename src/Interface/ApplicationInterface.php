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
	public static function configure(string $basePath): self;

	/**
	 * Set up routing paths for the application.
	 *
	 * @param string $api The path for API routes.
	 * @param string $web The path for web routes.
	 * @return self Returns the current instance of the implementing class.
	 */
	public function routing(string $api, string $web): self;

	/**
	 * Create the application by loading configuration files and routes.
	 *
	 * @return void
	 */
	public function create(): void;
}
