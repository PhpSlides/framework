<?php

namespace PhpSlides\Forgery;

use DB;
use PhpSlides\Logger\DBLogger;
use PhpSlides\Database\Connection;
use PhpSlides\Database\Database as DB_ORM;

/**
 * Abstract class for managing database operations.
 * 
 * This class provides methods for creating and dropping databases and tables.
 * It also includes error logging functionality via the DBLogger trait, which
 * logs the success or failure of operations.
 */
abstract class Database extends DB_ORM
{
    use DBLogger;

    /**
     * Create a database if it doesn't exist.
     *
     * This method checks if the database with the provided name exists. If not,
     * it creates the database and logs the operation.
     * 
     * @param string $db_name The name of the database to be created.
     */
    protected static function createDB($db_name)
    {
        try {
            // Initialize database connection
            Connection::init();

            // Check if the database already exists
            $query = DB::query(
                'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME=%s',
                $db_name
            );

            // If the database does not exist, create it
            if (empty($query)) {
                DB::query("CREATE DATABASE $db_name");
                static::log('INFO', "Created Database `$db_name`");
            }
        } catch (\Exception $e) {
            // Log any exceptions that occur during the database creation
            static::log(
                'ERROR',
                "Unable to create Database `$db_name`. [Exception]: {$e->getMessage()}"
            );
        }
    }

    /**
     * Drop a database if it exists.
     *
     * This method checks if the database exists. If it does, the database is dropped.
     * If the database does not exist, a warning is logged.
     * 
     * @param string $db_name The name of the database to be dropped.
     */
    protected static function dropDB($db_name)
    {
        try {
            // Initialize database connection
            Connection::init();

            // Check if the database exists
            $query = DB::query(
                'SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME=%s',
                $db_name
            );

            // If the database does not exist, log a warning
            if (empty($query)) {
                static::log(
                    'WARNING',
                    "Cannot drop unexisting database `$db_name`"
                );
                return;
            }

            // Drop the database
            DB::query("DROP DATABASE $db_name");
            static::log('INFO', "Dropped Database `$db_name`.");
        } catch (\Exception $e) {
            // Log any exceptions that occur during the database drop
            static::log(
                'ERROR',
                "Unable to drop Database `$db_name`. [Exception]: {$e->getMessage()}"
            );
        }
    }

    /**
     * Drop a table from a specified database.
     *
     * This method checks if the specified table exists in the given database. If it does,
     * the table is dropped. If the table does not exist, a warning is logged.
     * 
     * @param string $db_name The name of the database containing the table.
     * @param string $db_table The name of the table to be dropped.
     */
    protected static function dropTable($db_name, $db_table)
    {
        try {
            // Initialize database connection
            Connection::init();

            // Check if the table exists in the database
            $query = DB::query(
                'SELECT * FROM information_schema.tables WHERE table_schema=%s AND table_name=%s',
                $db_name,
                $db_table
            );

            // If the table does not exist, log a warning
            if (empty($query)) {
                static::log(
                    'WARNING',
                    "Cannot drop unexisting table `$db_table` in `$db_name` Database"
                );
                return;
            }

            // Drop the table
            DB::query("DROP TABLE $db_table");
            static::log(
                'INFO',
                "Dropped Table `$db_table` in `$db_name` Database."
            );
        } catch (\Exception $e) {
            // Log any exceptions that occur during the table drop
            static::log(
                'ERROR',
                "Unable to drop Table `$db_table` in `$db_name` Database. [Exception]: {$e->getMessage()}"
            );
        }
    }
}