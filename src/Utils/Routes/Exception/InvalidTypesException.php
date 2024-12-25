<?php declare(strict_types=1);

namespace PhpSlides\Core\Utils\Routes\Exception;

use Closure;
use PhpSlides\Core\Foundation\Application;

class InvalidTypesException extends \PhpSlides\Exception
{
   /**
    * @var array $types List of valid data types for route parameters.
    * 
    * The following types are supported:
    * - INT: Integer
    * - BOOL: Boolean
    * - JSON: JSON string
    * - ALPHA: Alphabetic characters
    * - ALNUM: Alphanumeric characters
    * - ARRAY: Array
    * - FLOAT: Floating point number
    * - STRING: String
    * - BOOLEAN: Boolean (alias for BOOL)
    * - INTEGER: Integer (alias for INT)
    */
   protected static array $types = [
   'INT',
   'BOOL',
   'JSON',
   'ALPHA',
   'ALNUM',
   'ARRAY',
   'FLOAT',
   'STRING',
   'BOOLEN',
   'INTEGER',
   ];


   /**
    * Catches invalid strict types and throws an exception if any are found.
    *
    * @param array|string $type The type(s) to check against the recognized URL parameter types.
    * @param ?Closure $message Optional closure to generate a custom exception message.
    *
    * @throws self If any of the provided types are not recognized as URL parameter types.
    */
   public static function catchInvalidStrictTypes (array|string $type, ?Closure $message = null): void
   {
      if (is_array($type))
      {
         foreach ($type as $t)
         {
            if (!in_array($t, self::$types))
            {
               if (!$message)
               {
                  throw new self("{{$t}} is not recognized as a URL parameter type");
               }
               else
               {
                  throw new self($message((string) $t));
               }
            }
         }
      }
      else
      {
         if (!in_array($type, self::$types))
         {
            if (!$message)
            {
               throw new self("{{$type}} is not recognized as a URL parameter type");
            }
            else
            {
               throw new self($message((string) $type));
            }
         }
      }
   }


   /**
    * Handles invalid parameter types by setting the HTTP response code and either
    * printing a custom error message or throwing an InvalidTypesException.
    *
    * @param array $typeRequested The types that were expected.
    * @param string $typeGotten The type that was actually received.
    * @param string|null $message Optional custom error message.
    * @param int $code The HTTP response code to set (default is 400).
    * 
    * @return InvalidTypesException
    */
   public static function catchInvalidParameterTypes (array $typeRequested, string $typeGotten, ?string $message = null, int $code = 400): InvalidTypesException
   {
      http_response_code($code);

      if (Application::$handleInvalidParameterType)
      {
         print_r((Application::$handleInvalidParameterType)($typeGotten));
         exit();
      }
      else
      {
         if (!$message)
         {
            $requested = implode(', ', $typeRequested);
            return new self(
             "Invalid request parameter type. {{$requested}} requested, but got {{$typeGotten}}",
            );
         }
         else
         {
            return new self($message);
         }
      }
   }
}