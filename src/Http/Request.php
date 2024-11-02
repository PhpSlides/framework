<?php declare(strict_types=1);

namespace PhpSlides\Http;

use stdClass;
use PhpSlides\Formatter\Validate;
use PhpSlides\Foundation\Application;
use PhpSlides\Http\Auth\Authentication;
use PhpSlides\Http\Interface\RequestInterface;

/**
 * Class Request
 *
 * Handles HTTP request data including URL parameters, query strings, headers, authentication, body data, and more.
 */
class Request extends Application implements RequestInterface
{
	use Authentication;
	use Validate;

	/**
	 * @var ?array The URL parameters.
	 */
	protected ?array $param;

	/**
	 * Request constructor.
	 *
	 * @param ?array $urlParam Optional URL parameters.
	 */
	public function __construct(?array $urlParam = null)
	{
		$this->param = $urlParam;
	}

	/**
	 * Returns URL parameters as an object.
	 *
	 * @param ?string $key If specified it'll get a particular parameter value using this $key
	 * and if not specified it'll return an object intries of all key & values
	 * @return object|string The URL parameters.
	 */
	public function urlParam(?string $key = null): object|string
	{
		if (!$key) {
			return (object) $this->param;
		}
		return $this->param[$key];
	}

	/**
	 * Parses and returns the query string parameters from the URL.
	 *
	 * @param ?string $name Get a particular query and if not specified, it'll list all queries as an object
	 * @return stdClass|string The parsed query parameters.
	 */
	public function urlQuery(?string $name = null): stdClass|string
	{
		$cl = new stdClass();
		$parsed = parse_url(self::$request_uri, PHP_URL_QUERY);

		if (!$parsed) {
			return $cl;
		}
		$parsed = mb_split('&', $parsed);

		$i = 0;
		while ($i < count($parsed)) {
			$p = mb_split('=', $parsed[$i]);
			$key = $p[0];
			$value = $p[1] ? $this->validate($p[1]) : null;

			$cl->$key = $value;
			$i++;
		}

		if (!$name) {
			return $cl;
		}
		return $cl->$name;
	}

	/**
	 * Retrieves headers from the request.
	 *
	 * @param ?string $name Optional header name to retrieve a specific header.
	 * @return mixed The headers, or a specific header value if $name is provided.
	 */
	public function headers(?string $name = null): mixed
	{
		$headers = getallheaders();

		if (!$name) {
			return (object) $this->validate($headers);
		}
		if (isset($headers[$name])) {
			return $this->validate($headers[$name]);
		} else {
			return null;
		}
	}

	/**
	 * Retrieves authentication credentials from the request.
	 *
	 * @return stdClass The authentication credentials.
	 */
	public function auth(): stdClass
	{
		$cl = new stdClass();
		$cl->basic = self::BasicAuthCredentials();
		$cl->bearer = self::BearerToken();

		return $cl;
	}

	/**
	 * Get the request body and if no parameter is specified,
	 * Parses and returns the body of the request as an associative array.
	 *
	 * @param ?string $name The particular request body to get
	 * @return mixed The request body data, or null if parsing fails.
	 */
	public function body(?string $name = null): mixed
	{
		$data = json_decode(file_get_contents('php://input'), true);

		if ($data === null || json_last_error() !== JSON_ERROR_NONE) {
			return null;
		}

		if ($name !== null) {
			return $this->validate($data[$name]);
		}
		return $this->validate($data);
	}

	/**
	 * Retrieves a GET parameter by key.
	 * And if no parameter is provided, returns all key and values in pairs
	 *
	 * @param ?string $key The key of the GET parameter.
	 * @return mixed The parameter value, or null if not set.
	 */
	public function get(?string $key = null): mixed
	{
		if (!$key) {
			return $this->validate($_GET);
		}
		if (!isset($_GET[$key])) {
			return null;
		}
		return $this->validate($_GET[$key]);
	}

	/**
	 * Retrieves a POST parameter by key.
	 * And if no parameter is provided, returns all key and values in pairs
	 *
	 * @param string $key The key of the POST parameter.
	 * @return mixed The parameter values, or null if not set.
	 */
	public function post(?string $key = null): mixed
	{
		if (!$key) {
			return $this->validate($_POST);
		}
		if (!isset($_POST[$key])) {
			return null;
		}

		$data = $this->validate($_POST[$key]);
		return $data;
	}

	/**
	 * Retrieves a request parameter by key from all input sources.
	 * And if no parameter is provided, returns all key and values in pairs
	 *
	 * @param ?string $key The key of the request parameter.
	 * @return mixed The parameter value, or null if not set.
	 */
	public function request(?string $key = null): mixed
	{
		if (!$key) {
			return $this->validate($_REQUEST);
		}
		if (!isset($_REQUEST[$key])) {
			return null;
		}

		$data = $this->validate($_REQUEST[$key]);
		return $data;
	}

	/**
	 * Retrieves file data from the request by name.
	 * And if no parameter is provided, returns all key and values in pairs
	 *
	 * @param ?string $name The name of the file input.
	 * @return object|null File data, or null if not set.
	 */
	public function files(?string $name = null): object|null
	{
		if (!$name) {
			return (object) $_FILES;
		}
		if (!isset($_FILES[$name])) {
			return null;
		}

		$files = $_FILES[$name];
		return (object) $files;
	}

	/**
	 * Retrieves a cookie value by key, or all cookies if no key is provided.
	 *
	 * @param ?string $key Optional cookie key.
	 * @return mixed The cookie value, all cookies as an object, or null if key is provided but not found.
	 */
	public function cookie(?string $key = null): mixed
	{
		if (!$key) {
			return (object) $this->validate($_COOKIE);
		}
		return isset($_COOKIE[$key]) ? $this->validate($_COOKIE[$key]) : null;
	}

	/**
	 * Retrieves a session value by key, or all session if no key is provided.
	 *
	 * @param ?string $key Optional cookie key.
	 * @return mixed The cookie value, all cookies as an object, or null if key is provided but not found.
	 */
	public function session(?string $key = null): mixed
	{
		session_status() < 2 && session_start();
		if (!$key) {
			return (object) $this->validate($_SESSION);
		}
		return isset($_SESSION[$key]) ? $this->validate($_SESSION[$key]) : null;
	}

	/**
	 * Retrieves the HTTP method used for the request.
	 *
	 * @return string The HTTP method (e.g., GET, POST).
	 */
	public function method(): string
	{
		return $_SERVER['REQUEST_METHOD'];
	}

	/**
	 * Retrieves the URI from the request.
	 *
	 * @return string The URI.
	 */
	public function uri(): string
	{
		return self::$request_uri;
	}

	/**
	 * Parses and returns URL components including query and parameters.
	 *
	 * @return object The parsed URL components.
	 */
	public function url(): object
	{
		$uri = $this->uri();
		$parsed = parse_url($uri);

		$parsed['query'] = (array) $this->urlQuery();
		$parsed['param'] = (array) $this->urlParam();

		return (object) $parsed;
	}

	/**
	 * Retrieves the client's IP address.
	 *
	 * @return string The client's IP address.
	 */
	public function ip(): string
	{
		return $this->validate($_SERVER['REMOTE_ADDR']);
	}

	/**
	 * Retrieves the client's user agent string.
	 *
	 * @return string The user agent string.
	 */
	public function userAgent(): string
	{
		return $this->validate($_SERVER['HTTP_USER_AGENT']);
	}

	/**
	 * Checks if the request was made via AJAX.
	 *
	 * @return bool True if the request is an AJAX request, false otherwise.
	 */
	public function isAjax(): bool
	{
		return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
			strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}

	/**
	 * Retrieves the URL of the referring page.
	 *
	 * @return string|null The referrer URL, or null if not set.
	 */
	public function referrer(): ?string
	{
		return isset($_SERVER['HTTP_REFERER'])
			? $this->validate($_SERVER['HTTP_REFERER'])
			: null;
	}

	/**
	 * Retrieves the server protocol used for the request.
	 *
	 * @return string The server protocol.
	 */
	public function protocol(): string
	{
		return $this->validate($_SERVER['SERVER_PROTOCOL']);
	}

	/**
	 * Retrieves all input data from GET, POST, and the request body.
	 *
	 * @return array The combined input data.
	 */
	public function all(): array
	{
		$data = array_merge($_GET, $_POST, $this->body() ?? []);
		return $this->validate($data);
	}

	/**
	 * Retrieves a parameter from the $_SERVER array.
	 * And if no parameter is provided, it returns all the keys and values in pairs
	 *
	 * @param string $key The key of the server parameter.
	 * @return mixed The server parameter value, or null if not set.
	 */
	public function server(?string $key = null): mixed
	{
		if (!$key) {
			return $this->validate($_SERVER);
		}
		return isset($_SERVER[$key]) ? $this->validate($_SERVER[$key]) : null;
	}

	/**
	 * Checks if the request method matches a given method.
	 *
	 * @param string $method The HTTP method to check.
	 * @return bool True if the request method matches, false otherwise.
	 */
	public function isMethod(string $method): bool
	{
		return strtoupper($this->method()) === strtoupper($method);
	}

	/**
	 * Checks if the request is made over HTTPS.
	 *
	 * @return bool True if the request is HTTPS, false otherwise.
	 */
	public function isHttps(): bool
	{
		return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on';
	}

	/**
	 * Retrieves the time when the request was made.
	 *
	 * @return int The request time as a Unix timestamp.
	 */
	public function requestTime(): int
	{
		return $_SERVER['REQUEST_TIME'];
	}
}
