<?php declare(strict_types=1);

namespace PhpSlides\Core\Parser;

use DB;
use PhpSlides\Core\Forgery\Forge;

/**
 * ORMParser is responsible for parsing a given class name into database-specific
 * formats and establishing a database connection based on the parsed class information.
 *
 * This parser is utilized within the ORM (Object-Relational Mapping) context,
 * where it maps class names to database names, enabling streamlined database
 * operations for models.
 */
class ORMParser extends Forge
{
	/**
	 * Parses a class name to determine the database and table names.
	 *
	 * This method extracts the database name from the class namespace and
	 * determines the table name from the class name itself. It then
	 * establishes a database connection to the parsed database.
	 *
	 * @param string $class The fully qualified class name, typically in the
	 *                      format `Namespace\Database\ClassName`.
	 * @return string The formatted table name, derived from the class name.
	 */
	public function parse(string $class)
	{
		// Extract the segments of the class namespace.
		$name = explode('\\', $class);

		// Format the second segment as the database name.
		$db_name = static::format($name[1]);

		// Format the last segment as the table name.
		$name = static::format(end($name));

		// Set the active database to the parsed database name.
		DB::useDB($db_name);

		// Return the table name for ORM use.
		return $name;
	}
}
