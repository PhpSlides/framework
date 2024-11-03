<?php

namespace PhpSlides\Formatter\Views;

/**
 * 
 */
trait FormatIncludes 
{
   
   /**
    * 
    */
   public function __construct()
   {
      // code...
   }
   
	/**
	 * Replaces all includes elements
	 */
	protected function includes()
	{
		$pattern = '/<!INCLUDE\S+path=["|\']([^"]+)["|\']\s*\/>/';

		$formattedContents = preg_replace_callback(
			$pattern,
			function ($matches) {
				$path = trim($matches[1]);
				return '<' .
					'? slides_include(__DIR__ . \'/' .
					$path .
					'\') ?' .
					'>';
			},
			$this->contents
		);
		$this->contents = $formattedContents;
	}
	
}