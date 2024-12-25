<?php

namespace PhpSlides\Core\Http\Interface;

interface AuthGuardInterface
{
	/**
	 * Abstract method to authorize the request.
	 *
	 * This method must be implemented by any class extending BaseAuthGuard. It should contain the logic
	 * to determine whether the request is authorized. The method should return `true` if the request is authorized,
	 * or `false` if it is not.
	 *
	 * @return bool Returns true if the request is authorized, false otherwise.
	 */
	public function authorize(): bool;
}
