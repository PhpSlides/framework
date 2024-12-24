<?php declare(strict_types=1);

namespace PhpSlides\Src\Utils\Routes\Exception;

use Closure;
use PhpSlides\Src\Foundation\Application;

class InvalidTypesException extends \PhpSlides\Exception
{
   private static array $types = [
   'INT',
   'BOOL',
   'JSON',
   'ALPHA',
   'ALNUM',
   'ARRAY',
   'FLOAT',
   'STRING',
   'INTEGER',
   ];

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
            $requested = implode(', ', $$typeRequested);
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