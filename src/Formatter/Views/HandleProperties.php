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
		$code = "<?php\nconst Props = $props;\n?>\n";
		$this->contents = $code . $this->contents;
	}
}
