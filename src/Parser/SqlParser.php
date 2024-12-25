<?php declare(strict_types=1);

namespace PhpSlides\Core\Parser;

use PhpSlides\Core\Logger\DBLogger;
use PhpSlides\Core\Formatter\SqlFormat;

/**
 * SqlParser is responsible for parsing SQL column definitions and constraints.
 *
 * This class parses the SQL column definition from a given path, processes
 * various column attributes such as type, length, default value, etc., and
 * formats them for database operations. It also handles logging of issues when
 * invalid column types or constraints are encountered.
 */
class SqlParser extends SqlFormat
{
	use DBLogger;

	/**
	 * Column types map to store various column attributes.
	 *
	 * This associative array holds different column-related attributes such
	 * as the column's name, type, length, default value, nullability, etc.
	 * Each key represents an attribute that can be defined in the SQL column
	 * declaration. Initially, all values are set to `null` and will be populated
	 * as the SQL file is parsed.
	 */
	protected $column_types = [
		'COLUMN_NAME' => null,
		'TYPE' => null,
		'LENGTH' => null,
		'UNSIGNED' => null,
		'ZEROFILL' => null,
		'CHARACTER' => null,
		'COLLATION' => null,
		'NULL' => null,
		'DEFAULT' => null,
		'AUTO_INCREMENT' => null,
		'UNIQUE' => null,
		'PRIMARY' => null,
		'INDEX' => null,
		'CHECK' => null,
		'FOREIGN' => null,
		'REFERENCES' => null,
		'DELETE' => null,
		'UPDATE' => null,
		'COMMENT' => null,
		'VISIBLE' => null,
		'STORAGE' => null,
		'GENERATED' => null,
		'VIRTUAL' => null,
		'PERSISTENT' => null,
		'OTHERS' => null,
	];

	/**
	 * Parses the SQL column definition from a specified file.
	 *
	 * This method reads a file containing column definitions and constraints,
	 * processes each line to extract relevant information, and populates the
	 * `$column_types` array. It replaces placeholders like `__table__` and
	 * `__column__` with the actual table and column names. If an unknown column
	 * type is encountered, a warning is logged.
	 *
	 * @param string $column_name The name of the column being processed.
	 * @param string $path The path to the file containing column definitions.
	 * @param array $constraint Any constraints associated with the column.
	 * @param ?string $table_name The name of the table to which the column belongs.
	 *
	 * @return string The formatted SQL constraint for the column.
	 */
	public function parse(
		string $column_name,
		string $path,
		array $constraint,
		?string $table_name,
	) {
		$code = file($path);
		$this->column_types['COLUMN_NAME'] = $column_name;

		foreach ($code as $value) {
			if (str_starts_with(trim($value), '#')) {
				continue;
			}

			$value = explode('#', $value)[0];
			$v = explode('=>', $value);
			$type = trim($v[0]);
			$value = trim(end($v));
			$value = str_replace('__table__', $table_name, $value);
			$value = str_replace('__column__', $column_name, $value);

			if (array_key_exists($type, $this->column_types)) {
				$this->column_types[$type] = $value;
			} else {
				self::log(
					'WARNING',
					"`$type` key does not exist in `$column_name` column type",
				);
			}
		}

		// Return the formatted SQL constraint for the column.
		return static::format($constraint);
	}
}
