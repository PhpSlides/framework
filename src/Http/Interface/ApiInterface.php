<?php

namespace PhpSlides\Core\Http\Interface;

interface ApiInterface
{
	/**
	 * Assigns a name to the last registered route for easier reference.
	 *
	 * @param string $name The name to assign to the route.
	 * @return self
	 */
	public function name (string $name): self;

	/**
	 * Defines a new route with a URL and a controller.
	 *
	 * @param string $url The Base URL of the route.
	 * @param string|array|null $controller The controller handling the route.
	 * @param ?string $controller The request method the route is going to accept,
	 * if null is given, then it's consider dynamic, accepts all methods.
	 * @return self
	 */
	public function route (
	 string $url,
	 string|array|null $controller = null,
	 ?string $req_method = null,
	): self;

	/**
	 * Applies Authentication Guard to the current route.
	 *
	 * @param ?string ...$guards String parameters of registered guards.
	 * @return self
	 */
	public function withGuard (?string ...$guards): self;

	/**
	 * Defines a base URL and controller for subsequent route mappings.
	 *
	 * @param string $url The base URL for the routes.
	 * @param string $controller The controller handling the routes.
	 * @return self
	 */
	public function define (string $url, string $controller): self;

	/**
	 * Maps multiple HTTP methods to a URL with their corresponding controller methods.
	 *
	 * @param array An associative array where the key is the route and the value is an array with the HTTP method and controller method.
	 * @return self
	 */
	public function map (array $rest_url): self;

	public static function v1 (): self;
	public static function v1_0 (): self;
}