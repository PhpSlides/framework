<?php declare(strict_types=1);

namespace PhpSlides\Traits\Resources;

use PhpSlides\MapRoute;
use PhpSlides\Exception;
use PhpSlides\Http\Request;
use PhpSlides\Loader\FileLoader;

trait ApiResources
{
	protected static array|bool $map_info = false;

	protected static ?array $route = null;

	protected static ?array $apiMap = null;

	protected function __route(): void
	{
		$match = new MapRoute();
		self::$map_info = $match->match(
			self::$route['r_method'] ?? 'dynamic',
			self::$route['url'] ?? ''
		);

		if (self::$map_info) {
			self::__api_guards(self::$route['guards'] ?? null);

			print_r(self::__routeSelection());
			exit();
		}
	}

	protected function __routeSelection(?Request $request = null)
	{
		$info = self::$map_info;
		$route = self::$route ?? self::$apiMap;

		$method = $_SERVER['REQUEST_METHOD'];
		$controller = $route['controller'] ?? '';

		if (!class_exists($controller)) {
			throw new Exception(
				"Api controller class `$controller` does not exist."
			);
		}
		$params = $info['params'] ?? null;

		if (!class_exists($controller)) {
			throw new Exception(
				"Api controller class does not exist: `$controller`"
			);
		}
		$cc = new $controller();

		$r_method = '';
		$method = strtoupper($_SERVER['REQUEST_METHOD']);

		if (isset($route['c_method'])) {
			$r_method = $route['c_method'];
			goto EXECUTE;
		}

		switch ($method) {
			case 'GET':
				global $r_method;
				$r_method = $params === null ? 'index' : 'show';
				break;

			case 'POST':
				$r_method = 'store';
				break;

			case 'PUT':
				$r_method = 'update';
				break;

			case 'PATCH':
				$r_method = 'patch';
				break;

			case 'DELETE':
				$r_method = 'destroy';
				break;

			default:
				if (method_exists($cc, '__default')) {
					$r_method = '__default';
				} else {
					http_response_code(405);
					self::log();
					exit('Request method not allowed.');
				}
				break;
		}

		EXECUTE:
		if ($request === null) {
			$request = new Request($params);
		}

		if (method_exists($cc, $r_method)) {
			$response = $cc->$r_method($request);
		}

		$r_method = 'error';
		$response = !$response ? $cc->$r_method($request) : $response;

		self::log();
		return $response;
	}

	protected function __api_guards(?array $guards): bool
	{
		if (!$guards) {
			return true;
		}

		$params = self::$map_info['params'] ?? null;
		$request = new Request($params);

		for ($i = 0; $i < count((array) $guards); $i++) {
			$registered_guards = (new FileLoader())
				->load(__DIR__ . '/../../Config/guards.php')
				->getLoad();

			if (array_key_exists($guards[$i], $registered_guards)) {
				$guard = $registered_guards[$guards[$i]];
			} else {
				self::log();
				throw new Exception(
					'No Registered AuthGuard as `' . $guards[$i] . '`'
				);
			}

			if (!class_exists($guard)) {
				self::log();
				throw new Exception("AuthGuard class does not exist: `{$guard}`");
			}
			$cl = new $guard($request);

			if ($cl->authorize() !== true) {
				self::log();
				exit();
			}
		}
			return true;
	}

	protected function __api_map(?Request $request = null): void
	{
		$map = self::$apiMap;
		$base_url = $map['base_url'] ?? '';
		$controller = $map['controller'] ?? '';

		foreach ($map as $route => $method) {
			$r_method = $method[0] ?? 'GET';
			$c_method = $method[1] ?? '';
			$guards = $method[2] ?? null;
			$url = $base_url . trim($route, '/');

			self::$apiMap = [
				'controller' => $controller,
				'c_method' => trim($c_method, '@'),
				'url' => $base_url
			];

			$match = new MapRoute();
			self::$map_info = $match->match($r_method, $url);

			if (self::$map_info) {
				$this->__api_guards($guards);

				print_r(self::__routeSelection());
				exit();
			}
		}
	}
}
