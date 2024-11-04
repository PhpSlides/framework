<?php declare(strict_types=1);

namespace PhpSlides;

/**
 * Handles Properties for view files
 */
class Props
{
	private static $properties = [];

	/**
	 *
	 */
	public function __set($key, $value)
	{
		self::$properties[$key] = $value;
	}

	/**
	 *
	 */
	public function __get($key)
	{
		if (array_key_exists($key, self::$properties)) {
			return self::$properties[$key];
		}
		throw new Exception("Property name `$key` does not exist.");
	}

	/**
	 *
	 */
	public static function all()
	{
		return static::$properties;
	}
}
