<?php declare(strict_types=1);

namespace PhpSlides\Core\Formatter;

use PhpSlides\Core\Formatter\Views\FormatPslTags;
use PhpSlides\Core\Formatter\Views\FormatIncludes;
use PhpSlides\Core\Formatter\Views\FormatHotReload;
use PhpSlides\Core\Formatter\Views\HandleProperties;
use PhpSlides\Core\Formatter\Views\FormatImportQuotes;
use PhpSlides\Core\Formatter\Views\FormatBracketInterpolation;

/**
 * Handles the formatting of view files for PhpSlides.
 *
 * This class is responsible for processing various formatting tasks on the view file contents.
 * It applies multiple transformations such as handling includes, hot reloads, import quotes,
 * bracket interpolation, PSL tags, and properties for dynamic rendering.
 *
 * @package PhpSlides\Core\Formatter
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

	/** @var string The contents of the view file to be formatted. */
	protected string $contents;

	/**
	 * ViewFormatter constructor.
	 *
	 * Initializes the formatter with the contents of the view file and applies various formatting tasks.
	 *
	 * @param string $contents The raw contents of the view file to be formatted.
	 */
	public function __construct (string $contents)
	{
		$this->contents = $contents;

		// Apply various formatting operations
		$this->includes();
		$this->hot_reload();
		$this->import_quotes();
		$this->bracket_interpolation();
		$this->psl_tags();
	}

	/**
	 * Resolves properties and returns the formatted contents.
	 *
	 * This method allows the injection of dynamic properties into the view, resolving them
	 * and returning the final formatted contents of the view.
	 *
	 * @param mixed ...$props The properties to be injected into the view.
	 *
	 * @return string The formatted contents of the view file with properties applied.
	 */
	public function resolve (mixed ...$props)
	{
		// Apply properties handling to the view
		$this->properties(...$props);

		// Return the final formatted contents
		return $this->contents;
	}
}
