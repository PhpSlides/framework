<?php declare(strict_types=1);

namespace PhpSlides\Utils\Routes;

use PhpSlides\Exception;
use PhpSlides\Foundation\Application;

trait StrictTypes
{
   private static array $types = [
   'INT',
   'JSON',
   'FLOAT',
   'ARRAY',
   'STRING',
   'INTEGER',
   'BOOLEAN',
   ];

   /**
    * 
    * @param string[] $types
    * @param string $haystack
    * @return void
    */
   protected static function matchType (array $types, string $haystack)
   {
      $typeofHaystack = self::typeOfString($haystack);

      foreach ($types as $type)
      {
      }

      print_r((Application::$handleInvalidParameterType)($typeofHaystack));
      http_response_code(400);
      $requested = implode(', ', $types);
      throw new Exception("Invalid request parameter type. {{$requested}} requested, but got {{$typeofHaystack}}");
   }

   private static function typeOfString (string $string)
   {
      return gettype($string);
   }
}