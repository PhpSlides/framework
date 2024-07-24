<?php declare(strict_types=1);

namespace PhpSlides\Foundation;

use PhpSlides\Loader\FileLoader;

class Application
{
	const PHPSLIDES_VERSION = '1.0.0';

	public static string $basePath;
	public static string $apiPath;
	public static string $webPath;

	public static string $configsDir;
	public static string $viewsDir;
	public static string $stylesDir;
	public static string $scriptsDir;

	public static function configure(string $rootPath): self
	{
		self::$basePath = $rootPath . '/';
		return new self();
	}

	public function routing(string $api, string $web): self
	{
		self::$apiPath = $api;
		self::$webPath = $web;

		self::$configsDir = self::$basePath . 'configs/';
		self::$viewsDir = self::$basePath . 'resources/views/';
		self::$stylesDir = self::$basePath . 'resources/src/styles/';
		self::$scriptsDir = self::$basePath . 'resources/src/scripts/';

		return $this;
	}

	public function create(): void
	{
		(new FileLoader())
			->load(__DIR__ . '/../Config/env.config.php')
			->load(__DIR__ . '/../Config/config.php')
			->load(self::$apiPath)
			->load(self::$webPath);
	}
}
