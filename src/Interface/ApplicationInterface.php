<?php declare(strict_types=1);

namespace PhpSlides\Src\Interface;

interface ApplicationInterface
{
	/**
	 * Create the application by loading configuration files and routes.
	 *
	 * @return void
	 */
	public function create(): void;
}
