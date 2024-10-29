<?php

namespace PhpSlides\Forgery;

use DB;
use PhpSlides\Parser\SqlParser;
use PhpSlides\Foundation\Application;

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
			$value = str_replace('../../', '', $value);
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
				$value = str_replace('../../', '', $value);
				$all_names = explode('/', $value);
				$table_name = end($all_names);
				$class = str_replace(
					'App\\',
					'',
					implode('\\', $all_names) . '\\' . $table_name
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

					static::log(
						'WARNING',
						"Ignored Table `$table_name` in `$db_name` Database."
					);
					continue;
				}

				$db_name = self::format($db_name);
				$table_name = self::format($table_name);

				$query = DB::query(
					'SELECT * FROM information_schema.tables WHERE table_schema=%s AND table_name=%s',
					$db_name,
					$table_name
				);

				if (!empty($query)) {
					continue;
				}

				$filePath = lcfirst($value);
				$filePath = glob("$filePath/*");
				$query = [];

				$constraint = [
					'PRIMARY' => null,
					'UNIQUE' => null,
					'INDEX' => null,
					'FOREIGN' => null,
					'REFERENCES' => null,
					'DELETE' => null,
					'UPDATE' => null,
					'OTHERS' => null
				];

				foreach ($filePath as $file) {
					if (!str_contains($file, '.')) {
						$res = (new SqlParser())->parse($file, $constraint);
						$query[] = $res[0];
						$constraint = $res[1];
					}
				}

				if ($constraint['PRIMARY']) {
					$key = implode(', ', $constraint['PRIMARY']);
					$query[] = "PRIMARY KEY ($key)";
				}

				if ($constraint['UNIQUE']) {
					$key = implode(', ', $constraint['UNIQUE']);
					$query[] = "UNIQUE ($key)";
				}

				if ($constraint['INDEX']) {
					$key = implode(', ', $constraint['INDEX']);
					$query[] = "INDEX ($key)";
				}

				if ($constraint['OTHERS']) {
					$key = implode(', ', $constraint['OTHERS']);
					$query[] = "$key";
				}

				if ($constraint['FOREIGN']) {
					foreach ($constraint['FOREIGN'] as $key) {
						$que = "FOREIGN KEY ($key)";

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
				DB::query("CREATE TABLE $table_name ($query)");
				static::log(
					'INFO',
					"Created Table `$table_name` in `$db_name` Database"
				);
			}
		} catch (\Exception $e) {
			static::log(
				'ERROR',
				"Unable to create Table `{$table_name}` in `$db_name` Database. [Exception]: {$e->getMessage()}"
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
}
