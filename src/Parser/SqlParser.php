<?php

namespace PhpSlides\Parser;

use PhpSlides\Logger\DBLogger;
use PhpSlides\Formatter\SqlFormat;

class SqlParser extends SqlFormat
{
	use DBLogger;

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
		'OTHERS' => null
	];

	public function parse(
		string $column_name,
		string $path,
		array $constraint,
		?string $table_name
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
					"`$type` key does not exist in `$column_name` column type"
				);
			}
		}

		return static::format($constraint);
	}
}
