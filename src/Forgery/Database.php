<?php

namespace PhpSlides\Forgery;

use DB;
use PhpSlides\Logger\DBLogger;
use PhpSlides\Database\Connection;
use PhpSlides\Database\Database as DB_ORM;

abstract class Database extends DB_ORM
{
	use DBLogger;

	protected static function createDB($db_name)
	{
		try {
			Connection::init();
			$query = DB::query(
				'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME=%s',
				$db_name
			);

			if (empty($query)) {
				DB::query("CREATE DATABASE $db_name");
				static::log('INFO', "Created Database `$db_name`");
			}
		} catch (\Exception $e) {
			static::log(
				'ERROR',
				"Unable to create Database `$db_name`. [Exception]: {$e->getMessage()}"
			);
		}
	}

	protected static function dropDB($db_name)
	{
		try {
			Connection::init();
			$query = DB::query(
				'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME=%s',
				$db_name
			);

			if (empty($query)) {
				static::log(
					'WARNING',
					"Cannot drop unexisting database `$db_name`"
				);
				return;
			}

			DB::query("DROP DATABASE $db_name");
			static::log('INFO', "Dropped Database `$db_name`.");
		} catch (\Exception $e) {
			static::log(
				'ERROR',
				"Unable to drop Database `$db_name`. [Exception]: {$e->getMessage()}"
			);
		}
	}

	protected static function dropTable($db_name, $db_table)
	{
		try {
			Connection::init();
			$query = DB::query(
				'SELECT * FROM information_schema.tables WHERE table_schema=%s AND table_name=%s',
				$db_name,
				$db_table
			);

			if (empty($query)) {
				static::log(
					'WARNING',
					"Cannot drop unexisting table `$db_table` in `$db_name` Database"
				);
				return;
			}

			DB::query("DROP TABLE $db_table");
			static::log(
				'INFO',
				"Dropped Table `$db_table` in `$db_name` Database."
			);
		} catch (\Exception $e) {
			static::log(
				'ERROR',
				"Unable to drop Table `$db_table` in `$db_name` Database. [Exception]: {$e->getMessage()}"
			);
		}
	}
}
