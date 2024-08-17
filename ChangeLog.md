# Change Logs [v1.2.5]

## [Sun, 04-08-2024]

-  Updated Route & view to default Content-Type as text/html
-  Updated request log according the APP_DEBUG in .env file.

## [Tue, 06-08-2024]

-  Added the use of `{{ }}` for writing php in an html attributes
-  Added `session` method to `PhpSlides\Http\Request` class
-  Updated `Application::$request_uri` to contain only path
-  Updated all requests function in the `Request` methods parameters to be optional.

## [Sat, 17-08-2024]

-  Updated env configuration to load the minor env configuration files
-  Added safe loading to `FileLoader` and `ViewLoader` class
-  Updated Route Map `Route::map()` to allow multiple request methods in string seperated with `|`, eg. `'POST|GET'`
-  Added JWT web tokens management.
