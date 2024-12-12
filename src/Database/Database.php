<?php

namespace PhpSlides\Database;

use MeekroORM;
use PhpSlides\Parser\ORMParser;

/**
 * Abstract class for managing database operations with MeekroORM.
 *
 * The `Database` class extends the `MeekroORM` library to manage database 
 * interactions, such as queries and connection handling, within the context 
 * of the PhpSlides framework. It allows dynamic initialization of tables 
 * and provides a mechanism for debugging connection errors.
 */
abstract class Database extends MeekroORM
{
    /**
     * @var string|null $_connect_error
     * Stores connection error messages, if any occur during database connection.
     * The error is captured and made available for debugging purposes.
     */
    public static ?string $_connect_error = null;

    /**
     * Initialize the current table in using non-static mode.
     *
     * The constructor invokes the `static()` method to initialize the current
     * table. It ensures that the class is configured for ORM operations, 
     * including setting up the table name.
     */
    public function __construct ()
    {
        static::static();
    }

    /**
     * Initialize the current table in using static mode.
     *
     * This static method parses the class name using the `ORMParser` to 
     * determine the table name and other relevant details. It assigns the 
     * table name to the static property `$_tablename` for use with MeekroORM.
     *
     * @return void
     */
    public static function static()
    {
        // Parse the class name using the ORMParser to determine the table name
        $parsed = (new ORMParser())->parse(get_called_class());

        // Assign the parsed table name to the static property $_tablename
        static::$_tablename = $parsed;
    }
}