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
 * This class provides an abstraction for interacting with the HTTP request in a structured way.
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
	 * Initializes the request object and optionally accepts URL parameters.
	 *
	 * @param ?array $urlParam Optional URL parameters.
	 */
	public function __construct(?array $urlParam = null)
	{
		$this->param = $urlParam;
	}

	/**
	 * Returns URL parameters as an object or a specific parameter if key is provided.
	 *
	 * This method retrieves the URL parameters for the current request. If a key is provided, it returns the
	 * value for that parameter; otherwise, it returns all parameters as an object.
	 *
	 * @param ?string $key If specified, retrieves the value of the given parameter key.
	 * @return object|string The URL parameters or a specific parameter value.
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
	 * This method parses the query string of the request URL and returns it as an object. If a name is specified,
	 * it will return the specific query parameter value.
	 *
	 * @param ?string $name If specified, returns a specific query parameter by name.
	 * @return stdClass|string The parsed query parameters or a specific parameter value.
	 */
	public function urlQuery(?string $name = null): stdClass|string
	{
		if (php_sapi_name() == 'cli-server') {
			$parsed = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
		} else {
			$parsed = parse_url(
				$_REQUEST['uri'] ?? $_SERVER['REQUEST_URI'],
				PHP_URL_QUERY,
			);
		}

		$cl = new stdClass();

		if (!$parsed) {
			return $cl;
		}
		$parsed = mb_split('&', urldecode($parsed));

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
	 * This method returns the headers sent with the HTTP request. If a specific header name is provided,
	 * it will return the value of that header; otherwise, it returns all headers as an object.
	 *
	 * @param ?string $name The header name to retrieve. If omitted, returns all headers.
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
	 * This method retrieves the authentication credentials from the request, including both Basic Auth and Bearer token.
	 * Returns an object with `basic` and `bearer` properties containing the respective credentials.
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
	 * Retrieves the request body as an associative array.
	 *
	 * This method parses the raw POST body data and returns it as an associative array.
	 * If a specific parameter is provided, it returns only that parameter's value.
	 *
	 * @param ?string $name The name of the body parameter to retrieve.
	 * @return mixed The body data or null if parsing fails.
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
	 *
	 * This method retrieves the value of a GET parameter by key. If no key is specified, it returns all GET parameters
	 * as an object.
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
	 *
	 * This method retrieves the value of a POST parameter by key. If no key is specified, it returns all POST parameters
	 * as an object.
	 *
	 * @param ?string $key The key of the POST parameter.
	 * @return mixed The parameter value, or null if not set.
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
	 *
	 * This method retrieves the value of a request parameter (from GET, POST, or the request body).
	 * If no key is specified, it returns all parameters as an object.
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
	 *
	 * This method retrieves file data from the request. If a name is provided, it returns the file data for that specific
	 * input field; otherwise, it returns all file data as an object.
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
	 * This method retrieves a specific cookie by its key. If no key is provided, it returns all cookies as an object.
	 *
	 * @param ?string $key The key of the cookie.
	 * @return mixed The cookie value, or null if not set.
	 */
	public function cookie(?string $key = null): mixed
	{
		if (!$key) {
			return (object) $this->validate($_COOKIE);
		}
		return isset($_COOKIE[$key]) ? $this->validate($_COOKIE[$key]) : null;
	}

	/**
	 * Retrieves a session value by key, or all session data if no key is provided.
	 *
	 * This method retrieves a specific session value by key. If no key is specified, it returns all session data as an object.
	 * It ensures that the session is started before accessing session data.
	 *
	 * @param ?string $key The key of the session value.
	 * @return mixed The session value, or null if not set.
	 */
	public function session(?string $key = null): mixed
	{
		// Start the session if it's not already started
		session_status() < 2 && session_start();

		// If no key is provided, return all session data as an object
		if (!$key) {
			return (object) $this->validate($_SESSION);
		}

		// If the session key exists, return its value; otherwise, return null
		return isset($_SESSION[$key]) ? $this->validate($_SESSION[$key]) : null;
	}

	/**
	 * Retrieves the HTTP request method (GET, POST, PUT, DELETE, etc.).
	 *
	 * This method provides the HTTP request method used in the current request, e.g., "GET", "POST", "PUT", etc.
	 *
	 * @return string The HTTP method of the request.
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
	 * Retrieves the IP address of the client making the request.
	 *
	 * This method returns the IP address of the client that initiated the request, taking into account possible proxies or load balancers.
	 *
	 * @return string The client's IP address.
	 */
	public function ip(): string
	{
		// Check for forwarded IP addresses from proxies or load balancers
		if ($_SERVER['HTTP_X_FORWARDED_FOR'] !== null) {
			return $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Retrieves the user agent string from the request headers.
	 *
	 * This method returns the value of the `User-Agent` header, which typically contains information about the client's browser and operating system.
	 *
	 * @return string The user agent.
	 */
	public function userAgent(): string
	{
		return $_SERVER['HTTP_USER_AGENT'];
	}

	/**
	 * Checks if the current request is an AJAX request.
	 *
	 * This method determines if the current request was made via AJAX by checking the value of the `X-Requested-With` header.
	 *
	 * @return bool Returns true if the request is an AJAX request, otherwise false.
	 */
	public function isAjax(): bool
	{
		return $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
	}

	/**
	 * Retrieves the URL of the referring page.
	 *
	 * @return string|null The referrer URL, or null if not set.
	 */
	public function referrer(): ?string
	{
		return $_SERVER['HTTP_REFERER'] !== null
			? $_SERVER['HTTP_REFERER']
			: null;
	}

	/**
	 * Retrieves the server protocol used for the request.
	 *
	 * @return string The server protocol.
	 */
	public function protocol(): string
	{
		return $_SERVER['SERVER_PROTOCOL'];
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
		return $_SERVER[$key] !== null ? $this->validate($_SERVER[$key]) : null;
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
		return $_SERVER['HTTPS'] !== null && ($_SERVER['HTTPS'] === 'on' || $_SERVER['SERVER_PORT'] == 443);
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

	/**
	 * Returns the content type of the request.
	 *
	 * This method returns the value of the `Content-Type` header, which indicates the type of data being sent in the request.
	 *
	 * @return string|null The content type, or null if not set.
	 */
	public function contentType(): ?string
	{
		return $this->headers('Content-Type');
	}

	/**
	 * Returns the length of the request's body content.
	 *
	 * This method returns the value of the `Content-Length` header, which indicates the size of the request body in bytes.
	 *
	 * @return int|null The content length, or null if not set.
	 */
	public function contentLength(): ?int
	{
		return $_SERVER['CONTENT_LENGTH'] !== null
			? (int) $_SERVER['CONTENT_LENGTH']
			: null;
	}
}
