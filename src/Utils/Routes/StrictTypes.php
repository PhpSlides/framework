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
   protected static function matchType (array $types, string $haystack): bool
   {
      $typeofHaystack = self::typeOfString($haystack);

      foreach ($types as $type)
      {
         $type = $type == 'INTEGER' ? 'INT' : strtoupper(trim($type));

         if (!in_array($type, self::$types))
         {
            throw new Exception("$type is not recognized as a URL parameter type");
         }

         if (strtoupper($type) === $typeofHaystack)
         {
            return true;
         }
      }

      http_response_code(400);
      if (Application::$handleInvalidParameterType)
      {
         print_r((Application::$handleInvalidParameterType)($typeofHaystack));
         exit;
      }
      else
      {
         $requested = implode(', ', $types);
         throw new Exception("Invalid request parameter type. {{$requested}} requested, but got {{$typeofHaystack}}");
      }
   }

   private static function typeOfString (string $string)
   {
      if (is_numeric($string))
      {
         if (strpos($string, '.') !== false)
         {
            return 'FLOAT';
         }
         else
         {
            return 'INT';
         }
      }
      else if (is_array(json_decode($string, true)))
      {
         return 'JSON';
      }
      else if (is_array($string))
      {
         return 'ARRAY';
      }
      else if (is_bool($string) || $string === 'true' || $string === 'false')
      {
         return 'BOOLEAN';
      }
      else
      {
         return 'STRING';
      }
   }
}