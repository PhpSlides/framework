<?php

namespace PhpSlides\Src\Formatter\Views;

/**
 * Trait to format import quotes in PhpSlides view files.
 * 
 * This trait modifies import statements in the view files, ensuring that the file paths
 * are correctly prefixed with `__DIR__` to resolve paths relative to the current directory.
 */
trait FormatImportQuotes
{
    /**
     * Constructor.
     * 
     * This constructor is a placeholder for any necessary initialization for
     * the class using this trait. It currently does not perform any operations.
     */
    public function __construct()
    {
        // code...
    }

    /**
     * Formats import quotes in the view file contents.
     * 
     * This method searches for `import('` and `import("` statements in the contents 
     * and prepends them with `__DIR__ . '/'` to resolve the paths relative to the 
     * current directory.
     */
    protected function import_quotes()
    {
        // Replace single quotes around 'import' statements with __DIR__ prefix
        $formattedContents = str_replace(
            'import(\'',
            'import(__DIR__ . \'/',
            $this->contents
        );
        
        // Replace double quotes around 'import' statements with __DIR__ prefix
        $formattedContents = str_replace(
            'import("',
            'import(__DIR__ . "/',
            $formattedContents
        );

        // Update the contents with the formatted import paths
        $this->contents = $formattedContents;
    }
}