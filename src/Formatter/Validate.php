<?php

namespace PhpSlides\Formatter;

trait Validate
{
   /**
    * Validate and get the type of value
    */
   protected function validate ($value): bool|float|int|string
   {
      // Convert to string for validation
      $validatedValue = (string) $value;

      // Sanitize the string using htmlspecialchars
      $sanitizedValue = htmlspecialchars($validatedValue, ENT_NOQUOTES);

      // Convert back to original type using gettype
      switch (gettype($value))
      {
         case 'integer':
            $convertedValue = (int) $sanitizedValue;
            break;
         case 'double':
            $convertedValue = (float) $sanitizedValue;
            break;
         case 'boolean':
            $convertedValue = (bool) $sanitizedValue;
            break;
         default:
            $convertedValue = $sanitizedValue; // remains a string
      }

      return $convertedValue;
   }
}