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

	public function parse($file, $constraint)
	{
		$code = file($file);
		$name = explode('/', $file);

		$column_name = explode('-', end($name));
		$column_name = $column_name[1] ?? $column_name[0];
		$this->column_types['COLUMN_NAME'] = $column_name;

		foreach ($code as $value) {
			if (str_starts_with(trim($value), '#')) {
				continue;
			}

			$value = explode('#', $value)[0];
			$v = explode('=>', $value);
			$type = trim($v[0]);
			$value = trim($v[1]);
			$value = str_replace('%this%', $column_name, $value);

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
