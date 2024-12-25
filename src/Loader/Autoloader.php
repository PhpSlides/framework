<?php

namespace PhpSlides\Core\Loader;

use PhpSlides\Core\Foundation\Application;

/**
 * The Autoloader class automatically loads ORM-related classes by scanning
 * a specific directory structure. This ensures required classes are available
 * without explicit imports, supporting a streamlined application flow.
 */
class Autoloader
{
	/**
	 * Initializes the Autoloader.
	 *
	 * The constructor triggers the ORM loader, which scans designated directories
	 * to find and instantiate ORM classes at runtime.
	 */
	public function __construct ()
	{
		self::ORMLoad();
	}

	/**
	 * Loads ORM classes dynamically.
	 *
	 * This method searches for ORM-related PHP files in a structured directory,
	 * converts each file path to a class namespace, and instantiates the class
	 * if it exists. This approach enables automated loading of ORM components
	 * without manual inclusion, making the application structure modular and
	 * maintainable.
	 */
	protected static function ORMLoad ()
	{
		foreach (glob(Application::$basePath . 'App/Forgery/*/*/*.php') as $value)
		{
			$class = explode('.', $value);
			$class = str_replace([ 'App/', '/' ], [ '', '\\' ], $class[0]);

			if (class_exists($class))
			{
				new $class();
			}
		}
	}
}