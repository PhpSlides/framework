<?php

namespace PhpSlides\Loader;

class Autoloader
{
	public function __construct()
	{
		self::ORMLoad();
	}

	protected static function ORMLoad()
	{
		foreach (glob('App/Forge/*/*/*.php') as $value) {
			$class = explode('.', $value);
			$class = str_replace(['App/', '/'], ['', '\\'], $class[0]);

			if (class_exists($class)) {
				new $class();
			}
		}
	}
}
