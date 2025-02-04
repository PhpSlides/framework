<?php declare(strict_types=1);

namespace PhpSlides\Core\Interface;

interface ApplicationInterface
{
	/**
	 * Create the application by loading configuration files and routes.
	 *
	 * @return void
	 */
	public function create (): void;
}
