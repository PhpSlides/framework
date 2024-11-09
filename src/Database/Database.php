<?php

namespace PhpSlides\Database;

use MeekroORM;
use PhpSlides\Parser\ORMParser;

abstract class Database extends MeekroORM
{
	/**
	 * When connecting to a database, if it gives error, it'll be debugged in here
	 */
	public static ?string $_connect_error = null;

	/**
	 * Initialize current table in using non static mode.
	 */
	public function __construct()
	{
		static::static();
	}

	/**
	 * Initialize current table in using static mode.
	 */
	public static function static()
	{
		$parsed = (new ORMParser())->parse(get_called_class());
		static::$_tablename = $parsed;
	}
}
