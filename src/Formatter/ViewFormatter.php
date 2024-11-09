<?php declare(strict_types=1);

namespace PhpSlides\Formatter;

use PhpSlides\Formatter\Views\FormatPslTags;
use PhpSlides\Formatter\Views\FormatIncludes;
use PhpSlides\Formatter\Views\FormatHotReload;
use PhpSlides\Formatter\Views\HandleProperties;
use PhpSlides\Formatter\Views\FormatImportQuotes;
use PhpSlides\Formatter\Views\FormatBracketInterpolation;

/**
 * Formatting of view files
 *
 * @author Dave Conco <info@dconco.dev>
 * @copyright 2024 Dave Conco
 */
class ViewFormatter
{
	use FormatPslTags;
	use FormatIncludes;
	use FormatHotReload;
	use HandleProperties;
	use FormatImportQuotes;
	use FormatBracketInterpolation;

	protected string $contents;

	/**
	 *
	 */
	public function __construct(string $contents)
	{
		$this->contents = $contents;

		$this->includes();
		$this->hot_reload();
		$this->import_quotes();
		$this->bracket_interpolation();
		$this->psl_tags();
	}

	/**
	 *
	 */
	public function resolve(mixed ...$props)
	{
		$this->properties(...$props);
		return $this->contents;
	}
}
