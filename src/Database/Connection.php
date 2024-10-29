<?php

namespace PhpSlides\Database;

use DB;

class Connection
{
	public static $dsn;
	public static $host;
	public static $port;
	public static $user;
	public static $db_name;
	public static $db_type;
	public static $password;

	static function connect()
	{
		static::$port = getenv('DB_PORT') ?: 3306;
		static::$host = getenv('DB_HOST') ?: '0.0.0.0';
		static::$user = getenv('DB_USER') ?: 'root';
		static::$db_name = getenv('DB_BASE') ?: '';
		static::$db_type = getenv('DB_CONN') ?: 'mysql';
		static::$password = getenv('DB_PASS') ?: '';

		DB::$dsn = sprintf(
			'%s:host=%s;port=%s;dbname=%s',
			static::$db_type,
			static::$host,
			static::$port,
			static::$db_name
		);
		DB::$user = static::$user;
		DB::$password = static::$password;
	}

	static function reconnect()
	{
		DB::disconnect();
		DB::$dsn = sprintf(
			'%s:host=%s;port=%s;dbname=%s',
			static::$db_type,
			static::$host,
			static::$port,
			static::$db_name
		);
	}

	static function init()
	{
		DB::$host = static::$host ?? getenv('DB_HOST') ?: 3306;
		DB::$user = static::$user ?? getenv('DB_USER') ?: 'root';
		DB::$password = static::$password ?? getenv('DB_PASS') ?: '';
	}
}
