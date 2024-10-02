<?php

namespace PhpSlides\Loader;

use PhpSlides\Foundation\Application;

class Autoloader
{
	public function __construct()
	{
		self::ORMLoad();
	}

	protected static function ORMLoad()
	{
		foreach (glob(Application::$basePath . 'App/Forgery/*/*/*.php') as $value) {
			$class = explode('.', $value);
			$class = str_replace(['App/', '/'], ['', '\\'], $class[0]);

			if (class_exists($class)) {
				new $class();
			}
		}
	}
}
