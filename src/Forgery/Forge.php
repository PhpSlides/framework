<?php declare(strict_types=1);

namespace PhpSlides\Src\Forgery;

use DB;
use PhpSlides\Src\Parser\SqlParser;
use PhpSlides\Src\Foundation\Application;

class Forge extends Database
{
	/**
	 * Search all files and directories that are in the `App/Forgery` directory.
	 * Get the Database name from the directory
	 * Get the Table name from the file and the directory
	 * will be that database for the table.
	 * Replace every uppercase letter with underscore(_)
	 * And the letter follows it. All falls in lowercase
	 */
	public function __construct()
	{
		foreach (glob(Application::$basePath . 'App/Forgery/*') as $value) {
			$value = ltrim($value, './');
			$db_name = str_replace('App/Forgery/', '', $value);
			$sdb_name = $db_name;

			# Ignoring Database
			if (
				str_starts_with($db_name, 'ignore-') ||
				str_contains($db_name, '.')
			) {
				$db_name = str_replace('ignore-', '', $db_name);
				$db_name = self::format($db_name);

				static::log('WARNING', "Ignored Database `$db_name`.");
			}
			# Drop Database
			elseif (str_starts_with($db_name, 'drop-')) {
				$db_name = str_replace('drop-', '', $db_name);
				$db_name = self::format($db_name);

				static::dropDB($db_name);
			}
			# Proceed in creating Database
			else {
				$db_name = self::format($db_name);

				static::createDB($db_name);
				self::table($sdb_name);
			}
		}
	}

	protected static function table($db_name)
	{
		$table_name = '';

		try {
			DB::useDB(self::format($db_name));
			$all_class = [];

			foreach (
				glob(Application::$basePath . "App/Forgery/$db_name/*")
				as $value
			) {
				$value = ltrim($value, './');
				$all_names = explode('/', $value);
				$table_name = end($all_names);
				$class = str_replace(
					'App\\',
					'',
					implode('\\', $all_names) . '\\' . $table_name,
				);

				# Drop Table
				if (str_starts_with($table_name, 'drop-')) {
					$table_name = str_replace('drop-', '', $table_name);
					$table_name = self::format($table_name);
					$db_name = self::format($db_name);

					static::dropTable($db_name, $table_name);
					continue;
				} elseif (
					str_starts_with($table_name, 'ignore-') ||
					str_contains($table_name, '.')
				) {
					$table_name = str_replace('ignore-', '', $table_name);
					$table_name = self::format($table_name);
					$db_name = self::format($db_name);

					if ($table_name != 'options.sql') {
						static::log(
							'WARNING',
							"Ignored Table `$table_name` in `$db_name` Database.",
						);
					}
					continue;
				}

				$db_name = self::format($db_name);
				$table_name = self::format($table_name);
				$table_already_exists = false;

				$query = DB::query(
					'SELECT * FROM information_schema.tables WHERE table_schema=%s AND table_name=%s',
					$db_name,
					$table_name,
				);

				if (!empty($query)) {
					$table_already_exists = true;
				}

				$filePath = Application::$basePath . lcfirst($value);
				$filePath = glob("$filePath/*.sql");
				$query = [];

				$constraint = [
					'PRIMARY' => null,
					'UNIQUE' => null,
					'INDEX' => null,
					'FOREIGN' => null,
					'REFERENCES' => null,
					'DELETE' => null,
					'UPDATE' => null,
					'OTHERS' => null,
				];

				$db_columns = [];
				if ($table_already_exists) {
					$db_columns = array_keys(DB::columnList($table_name));
				}

				/**
				 * Filter the array, if the column already exists in the database
				 * then remove it from the array of columns that will be created.
				 */
				$filePath = array_filter($filePath, function ($path) use (
					$table_already_exists,
					$db_columns,
				) {
					if ($table_already_exists) {
						$column_name = self::get_column_name($path);
						return in_array($column_name, $db_columns) ? false : true;
					}
					return true;
				});

				/**
				 * IF NO COLUMNS TO ADD, MOVE TO THE NEXT TABLE
				 */
				if (empty($filePath)) {
					continue;
				}

				/**
				 * Rearrange the array
				 */
				$filePath = array_values($filePath);

				$columns = array_map(function ($file) {
					return [self::get_column_name($file), $file];
				}, $filePath);

				$only_columns = array_map(function ($file) {
					return self::get_column_name($file);
				}, $filePath);

				for ($i = 0; $i < count($columns); $i++) {
					$res = (new SqlParser())->parse(
						column_name: $columns[$i][0],
						path: $columns[$i][1],
						constraint: $constraint,
						table_name: $table_name,
					);
					$query[] = $res[0];
					$constraint = $res[1];
				}

				if ($table_already_exists) {
					$query = array_map(function ($que) {
						return "ADD COLUMN $que";
					}, $query);
				}

				if ($constraint['PRIMARY']) {
					$key = implode(', ', (array) $constraint['PRIMARY']);
					$query[] = $table_already_exists
						? "ADD PRIMARY KEY ($key)"
						: "PRIMARY KEY ($key)";
				}

				if ($constraint['INDEX']) {
					$key = implode(', ', (array) $constraint['INDEX']);

					if ($constraint['UNIQUE']) {
						$query[] = $table_already_exists
							? "ADD UNIQUE INDEX ($key)"
							: "UNIQUE INDEX ($key)";
					} else {
						$query[] = $table_already_exists
							? "ADD INDEX ($key)"
							: "INDEX ($key)";
					}
				} elseif ($constraint['UNIQUE']) {
					$key = implode(', ', (array) $constraint['UNIQUE']);
					$query[] = $table_already_exists
						? "ADD UNIQUE ($key)"
						: "UNIQUE ($key)";
				}

				if ($constraint['OTHERS']) {
					$key = $table_already_exists
						? 'ADD ' . implode(', ', (array) $constraint['OTHERS'])
						: implode(', ', (array) $constraint['OTHERS']);
					$query[] = (string) $key;
				}

				if ($constraint['FOREIGN']) {
					foreach ((array) $constraint['FOREIGN'] as $key) {
						$que = $table_already_exists
							? "ADD FOREIGN KEY ($key)"
							: "FOREIGN KEY ($key)";

						if (isset($constraint['REFERENCES'][$key])) {
							$value = $constraint['REFERENCES'][$key];
							$que .= " REFERENCES $value";
						}

						if (isset($constraint['UPDATE'][$key])) {
							$value = $constraint['UPDATE'][$key];
							$que .= " ON UPDATE $value";
						}

						if (isset($constraint['DELETE'][$key])) {
							$value = $constraint['DELETE'][$key];
							$que .= " ON DELETE $value";
						}
						$query[] = $que;
					}
				}

				$query = implode(', ', $query);
				$only_columns = implode(', ', $only_columns);

				/**
				 * IF TABLE ALREADY EXISTS THEN IT'LL UPDATE THE COLUMNS
				 */
				if ($table_already_exists) {
					DB::query('ALTER TABLE %b %l', $table_name, $query);
					static::log(
						'INFO',
						"Altered Table `$table_name` and adds column `$only_columns`",
					);
				} else {
					DB::query('CREATE TABLE %b (%l)', $table_name, $query);
					static::log(
						'INFO',
						"Created Table `$table_name` in `$db_name` Database",
					);
				}
			}
		} catch (\Exception $e) {
			static::log(
				'ERROR',
				"Unable to create Table `$table_name` in `$db_name` Database. [Exception]: {$e->getMessage()}",
			);
			return;
		}
	}

	/**
	 * Format name
	 * Every uppercase letter will be converted to lowercase with an underscore(_)
	 *
	 * @param string $name The name to format
	 * @return string The replced name
	 */
	protected static function format(string $name): string
	{
		// Convert the variable to the desired format
		return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
	}

	/**
	 * Get the column name and parse it from file.
	 *
	 * @param string $path The file path to extract the name
	 */
	protected static function get_column_name(string $path): string
	{
		$name = explode('/', $path);

		$column_name = explode('-', end($name));
		$column_name = explode('.', $column_name[1] ?? $column_name[0]);
		return $column_name[0];
	}
}
