<?php declare(strict_types=1);

namespace PhpSlides\Http\Auth;

use PhpSlides\Http\Request;
use PhpSlides\Http\Interface\AuthGuardInterface;

/**
 * The Base AuthGuard
 *
 * An abstract class for implementing custom authentication guards.
 * This class provides a base structure for all guards, ensuring consistent handling of request objects
 * and defining a method that child classes must implement to authorize a request.
 *
 * Developers should extend this class to create their own authentication logic.
 */
abstract class AuthGuard implements AuthGuardInterface
{
	/**
	 * @var Request The request object that contains all the information about the incoming HTTP request.
	 */
	protected static Request $request;

	/**
	 * BaseAuthGuard constructor.
	 *
	 * Initializes the guard with the request object.
	 * This constructor ensures that all guards have access to the
	 * request data needed to perform their checks.
	 *
	 * @param Request $request The HTTP request object.
	 */
	public function __construct(Request $request)
	{
		self::$request = $request;
	}

	/**
	 * Abstract method to authorize the request.
	 *
	 * This method must be implemented by any class extending BaseAuthGuard. It should contain the logic
	 * to determine whether the request is authorized. The method should return `true` if the request is authorized,
	 * or `false` if it is not.
	 *
	 * @return bool Returns true if the request is authorized, false otherwise.
	 */
	abstract public function authorize(): bool;
}
