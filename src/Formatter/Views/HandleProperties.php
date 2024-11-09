<?php

namespace PhpSlides\Formatter\Views;

/**
 *
 */
trait HandleProperties
{
	/**
	 *
	 */
	public function __construct()
	{
		// code...
	}

	/**
	 * Add properties to view file
	 *
	 * @param mixed ...$props The Properties to be added
	 */
	protected function properties(mixed ...$props)
	{
		if (empty($props)) {
			return;
		}
		$code = '<' . '?php $s = new \PhpSlides\Props(); ';

		foreach ($props as $key => $value) {
			if (is_int($key)) {
				$key = "_$key";
			}

			if (is_int($value) || is_bool($value)) {
				$code .= '$s->' . "$key = $value; ";
			} elseif (is_array($value)) {
				$s = serialize($value);
				$code .= '$s->' . "$key = unserialize('$s'); ";
			} else {
				$code .= '$s->' . "$key = '$value'; ";
			}
		}
		$code .= '$s = null; ?' . '>';

		$this->contents = $code . $this->contents;
	}
}
