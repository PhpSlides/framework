<?php declare(strict_types=1);

namespace PhpSlides\Http\Interface;

use stdClass;

/**
 * Interface RequestInterface
 *
 * Defines the methods for handling HTTP request data.
 */
interface RequestInterface
{
	public function __construct(array $routeParam);

	/**
	 * Returns URL parameters as an object.
	 *
	 * @param ?string $key If specified it'll get a particular parameter value using this $key
	 * and if not specified it'll return an object intries of all key & values
	 * @return object|string The URL parameters.
	 */
	public function urlParam(?string $key = null): object|string;

	/**
	 * Parses and returns the query string parameters from the URL.
	 *
	 * @param ?string $name Get a particular query and if not specified, it'll list all queries as an object
	 * @return stdClass|string The parsed query parameters.
	 */
	public function urlQuery(?string $name = null): stdClass|string;

	/**
	 * Retrieves headers from the request.
	 *
	 * @param ?string $name Optional header name to retrieve a specific header.
	 * @return string|array|null The headers, or a specific header value if $name is provided.
	 */
	public function headers(?string $name = null): string|array|null;

	/**
	 * Retrieves authentication credentials from the request.
	 *
	 * @return stdClass The authentication credentials.
	 */
	public function auth(): stdClass;

	/**
	 * Get the request body and if no parameter is specified,
	 * Parses and returns the body of the request as an associative array.
	 *
	 * @param ?string $name The particular request body to get
	 * @return mixed The request body data, or null if parsing fails.
	 */
	public function body(?string $name = null): mixed;

	/**
	 * Retrieves a GET parameter by key.
	 * And if no parameter is provided, returns all key and values in pairs
	 *
	 * @param ?string $key The key of the GET parameter.
	 * @return mixed The parameter value, or null if not set.
	 */
	public function get(?string $key = null): mixed;

	/**
	 * Retrieves a POST parameter by key.
	 * And if no parameter is provided, returns all key and values in pairs
	 *
	 * @param string $key The key of the POST parameter.
	 * @return mixed The parameter values, or null if not set.
	 */
	public function post(?string $key = null): mixed;

	/**
	 * Retrieves a request parameter by key from all input sources.
	 * And if no parameter is provided, returns all key and values in pairs
	 *
	 * @param ?string $key The key of the request parameter.
	 * @return mixed The parameter value, or null if not set.
	 */
	public function request(?string $key = null): mixed;

	/**
	 * Retrieves file data from the request by name.
	 *
	 * @param string $name The name of the file input.
	 * @return ?object File data, or null if not set.
	 */
	public function files(string $name): ?object;

	/**
	 * Retrieves a cookie value by key, or all cookies if no key is provided.
	 *
	 * @param ?string $key Optional cookie key.
	 * @return mixed The cookie value, all cookies as an object, or null if key is provided but not found.
	 */
	public function cookie(?string $key = null): mixed;

	/**
	 * Retrieves a session value by key, or all session if no key is provided.
	 *
	 * @param ?string $key Optional cookie key.
	 * @return mixed The cookie value, all cookies as an object, or null if key is provided but not found.
	 */
	public function session(?string $key = null): mixed;

	/**
	 * Retrieves the HTTP method used for the request.
	 *
	 * @return string The HTTP method (e.g., GET, POST).
	 */
	public function method(): string;

	/**
	 * Retrieves the URI from the request.
	 *
	 * @return string The URI.
	 */
	public function uri(): string;

	/**
	 * Parses and returns URL components including query and parameters.
	 *
	 * @return object The parsed URL components.
	 */
	public function url(): object;

	/**
	 * Retrieves the client's IP address.
	 *
	 * @return string The client's IP address.
	 */
	public function ip(): string;

	/**
	 * Retrieves the client's user agent string.
	 *
	 * @return string The user agent string.
	 */
	public function userAgent(): string;

	/**
	 * Checks if the request was made via AJAX.
	 *
	 * @return bool True if the request is an AJAX request, false otherwise.
	 */
	public function isAjax(): bool;

	/**
	 * Retrieves the URL of the referring page.
	 *
	 * @return string|null The referrer URL, or null if not set.
	 */
	public function referrer(): ?string;

	/**
	 * Retrieves the server protocol used for the request.
	 *
	 * @return string The server protocol.
	 */
	public function protocol(): string;

	/**
	 * Retrieves all input data from GET, POST, and the request body.
	 *
	 * @return array The combined input data.
	 */
	public function all(): array;

	/**
	 * Retrieves a parameter from the $_SERVER array.
	 * And if no parameter is provided, it returns all the keys and values in pairs
	 *
	 * @param string $key The key of the server parameter.
	 * @return mixed The server parameter value, or null if not set.
	 */
	public function server(?string $key = null): mixed;

	/**
	 * Checks if the request method matches a given method.
	 *
	 * @param string $method The HTTP method to check.
	 * @return bool True if the request method matches, false otherwise.
	 */
	public function isMethod(string $method): bool;

	/**
	 * Checks if the request is made over HTTPS.
	 *
	 * @return bool True if the request is HTTPS, false otherwise.
	 */
	public function isHttps(): bool;

	/**
	 * Retrieves the time when the request was made.
	 *
	 * @return int The request time as a Unix timestamp.
	 */
	public function requestTime(): int;

	/**
	 * Returns the content type of the request.
	 *
	 * This method returns the value of the `Content-Type` header, which indicates the type of data being sent in the request.
	 *
	 * @return string|null The content type, or null if not set.
	 */
	public function contentType(): ?string;

	/**
	 * Returns the length of the request's body content.
	 *
	 * This method returns the value of the `Content-Length` header, which indicates the size of the request body in bytes.
	 *
	 * @return int|null The content length, or null if not set.
	 */
	public function contentLength(): ?int;
}
