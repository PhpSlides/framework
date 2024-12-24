<?php

namespace PhpSlides\Src\Formatter\Views;

/**
 * Trait to handle properties for view files.
 *
 * This trait allows properties to be injected into the view file dynamically.
 * It processes the given properties and adds them to the view file, enabling
 * their usage within the view's PHP code.
 */
trait HandleProperties
{
	/**
	 * Constructor.
	 *
	 * This constructor is a placeholder for any necessary initialization for
	 * the class using this trait. It currently does not perform any operations.
	 */
	public function __construct ()
	{
		// code...
	}

	/**
	 * Adds properties to the view file.
	 *
	 * This method adds properties to the view by creating a new `\PhpSlides\Props`
	 * object and assigning the passed properties to it. The properties can be of
	 * various types (integer, boolean, array, string), and are serialized or
	 * directly assigned depending on their type.
	 *
	 * @param mixed ...$props The properties to be added to the view.
	 *
	 * Each property is assigned to the `$s` object, which is an instance of the
	 * `\PhpSlides\Props` class. The final result is a code snippet that injects
	 * the properties into the view file for later use.
	 */
	protected function properties (mixed ...$props)
	{
		if (empty($props))
		{
			return;
		}

		// Initialize the PHP code snippet for assigning properties
		$code = '<' . '?php $s = new \PhpSlides\Src\Props(); ';

		// Loop through each provided property
		foreach ($props as $key => $value)
		{
			if (is_int($key))
			{
				// If key is an integer, prefix it with an underscore
				$key = "_$key";
			}

			// Handle different types of values (int, bool, array, string)
			if (is_int($value) || is_bool($value))
			{
				$code .= '$s->' . "$key = $value; ";
			}
			elseif (is_array($value))
			{
				// Serialize array values
				$s = serialize($value);
				$code .= '$s->' . "$key = unserialize('$s'); ";
			}
			else
			{
				$code .= '$s->' . "$key = '$value'; ";
			}
		}

		// Close the PHP code block and add the properties to the view content
		$code .= '$s = null; ?' . '>';

		// Prepend the generated code to the contents
		$this->contents = $code . $this->contents;
	}
}