<?php

namespace PhpSlides\Database;

use MeekroORM;
use PhpSlides\Parser\ORMParser;

abstract class Database extends MeekroORM
{
	public function __construct()
	{
	   static::static();
	}

	public static function static()
	{
		$parsed = (new ORMParser())->parse(get_called_class());
		static::$_tablename = $parsed;
	}
}
