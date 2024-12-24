<?php

namespace PhpSlides\Src\Database;

use DB;

/**
 * Class for managing database connections.
 *
 * The `Connection` class provides methods to connect, reconnect, and initialize
 * the database connection parameters. It manages the environment variables used
 * to configure the connection to a MySQL (or other DBMS) database.
 */
class Connection
{
	// Database connection parameters
	public static $dsn;
	public static $host;
	public static $port;
	public static $user;
	public static $db_name;
	public static $db_type;
	public static $password;

	/**
	 * Establish a connection to the database using environment variables.
	 *
	 * This method retrieves the database connection details from environment
	 * variables (e.g., `DB_HOST`, `DB_USER`, `DB_PASS`) and constructs the DSN
	 * for the connection. It then assigns the connection details to the static
	 * properties used by the DB class.
	 *
	 * @return void
	 */
	static function connect ()
	{
		// Set connection parameters from environment variables
		static::$port = getenv('DB_PORT') ?: 3306;
		static::$host = getenv('DB_HOST') ?: '0.0.0.0';
		static::$user = getenv('DB_USER') ?: 'root';
		static::$db_name = getenv('DB_BASE') ?: '';
		static::$db_type = getenv('DB_CONN') ?: 'mysql';
		static::$password = getenv('DB_PASS') ?: '';

		// Construct DSN (Data Source Name) for the database connection
		DB::$dsn = sprintf(
		 '%s:host=%s;port=%s;dbname=%s',
		 static::$db_type,
		 static::$host,
		 static::$port,
		 static::$db_name
		);

		// Set the user and password for the database connection
		DB::$user = static::$user;
		DB::$password = static::$password;
	}

	/**
	 * Reconnect to the database.
	 *
	 * This method disconnects the current database connection and re-establishes it
	 * using the updated connection parameters. It is useful for cases where the
	 * database parameters need to be refreshed.
	 *
	 * @return void
	 */
	static function reconnect ()
	{
		// Disconnect the current database connection
		DB::disconnect();

		// Recreate the DSN and reconnect with the new parameters
		DB::$dsn = sprintf(
		 '%s:host=%s;port=%s;dbname=%s',
		 static::$db_type,
		 static::$host,
		 static::$port,
		 static::$db_name
		);
	}

	/**
	 * Initialize the database connection parameters.
	 *
	 * This method ensures that the static properties of the `DB` class are initialized
	 * with values from the `Connection` class or the environment variables. It helps
	 * configure the connection before making any database queries.
	 *
	 * @return void
	 */
	static function init ()
	{
		DB::$host = static::$host ?? getenv('DB_HOST') ?: 3306;
		DB::$user = static::$user ?? getenv('DB_USER') ?: 'root';
		DB::$password = static::$password ?? getenv('DB_PASS') ?: '';
	}
}