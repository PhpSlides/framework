<?php declare(strict_types=1);

namespace PhpSlides;

/**
 * Class Props
 *
 * Handles the properties for view files, allowing dynamic property access and storage.
 * This class provides the ability to store properties and retrieve them by their key.
 * It also allows access to all properties at once.
 */
class Props
{
	/**
	 * @var array<string, mixed> $properties
	 * A static array holding all the properties. The keys are property names,
	 * and the values are the corresponding property values.
	 */
	private static $properties = [];

	/**
	 * Magic method for setting a property value.
	 * This method is invoked when an undefined property is written to.
	 *
	 * @param string $key The name of the property being set.
	 * @param mixed $value The value to assign to the property.
	 */
	public function __set(string $key, mixed $value)
	{
		self::$properties[$key] = $value;
	}

	/**
	 * Magic method for retrieving a property value.
	 * This method is invoked when an undefined property is accessed.
	 *
	 * @param string $key The name of the property to retrieve.
	 *
	 * @return mixed The value of the requested property.
	 *
	 * @throws Exception If the property does not exist.
	 */
	public function __get(string $key)
	{
		if (array_key_exists($key, self::$properties)) {
			return self::$properties[$key];
		}
		throw new Exception("Property name `$key` does not exist.");
	}

	/**
	 * Retrieves all properties as an associative array.
	 *
	 * @return array<string, mixed> An associative array containing all the properties and their values.
	 */
	public static function all(): array
	{
		return static::$properties;
	}
}
