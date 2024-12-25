<?php declare(strict_types=1);


namespace PhpSlides\Core\Cli;

trait Configure
{
   public static function bootstrap ()
   {
      if (php_sapi_name() == 'cli')
      {
         // Mock necessary $_SERVER variables
         $_SERVER['REQUEST_URI'] ??= '/';
         $_SERVER['REQUEST_METHOD'] ??= 'GET';
         $_SERVER['HTTP_HOST'] ??= 'localhost';
         $_SERVER['SERVER_NAME'] ??= 'localhost';
         $_SERVER['SERVER_PORT'] ??= '80';
         $_SERVER['HTTPS'] ??= 'off';
      }
   }
}