<?php declare(strict_types=1);

namespace PhpSlides\Http\Auth;

trait Authorization
{
    // Static variable to store the authorization header
    private static ?string $authorizationHeader = null;

    /**
     * Retrieves the Authorization header from the request.
     *
     * This method fetches all headers from the request and stores the Authorization header
     * in a static property for subsequent use in authentication methods.
     *
     * @return void
     */
    private static function getAuthorizationHeader (): void
    {
        $headers = getallheaders();
        self::$authorizationHeader = $headers['Authorization'] ?? $_SERVER['HTTP_AUTHORIZATION'] ?? null;
    }

    /**
     * Get Basic Authentication credentials from the Authorization header.
     *
     * This method checks the `Authorization` header for the Basic authentication scheme,
     * decodes the Base64 credentials, and splits them into the username and password.
     *
     * The username and password are returned as an associative array, or null if the 
     * authorization header is not found or does not contain Basic credentials.
     *
     * @return ?array Returns an associative array with 'username' and 'password' or null if not found.
     */
    public static function BasicAuthCredentials (): ?array
    {
        self::getAuthorizationHeader();

        // Check if the Authorization header is set and starts with "Basic"
        if (self::$authorizationHeader && strpos(self::$authorizationHeader, 'Basic ') === 0)
        {
            // Remove "Basic " from the header to extract base64 encoded credentials
            $base64Credentials = substr(self::$authorizationHeader, 6);

            // Decode the base64 credentials
            $decodedCredentials = base64_decode($base64Credentials, true);

            // Check if decoding was successful
            if ($decodedCredentials === false)
            {
                return null; // Return null if decoding fails
            }

            // Split the decoded string into username and password
            [ $username, $password ] = explode(':', $decodedCredentials, 2);

            // Return the credentials as an associative array
            return [
                'username' => trim(htmlspecialchars($username)),
                'password' => trim(htmlspecialchars($password)),
            ];
        }

        // Return null if no valid Basic Auth credentials are found
        return null;
    }

    /**
     * Get Bearer Token from the Authorization header.
     *
     * This method checks the `Authorization` header for the Bearer authentication scheme
     * and returns the token if found.
     *
     * @return ?string Returns the token as a string, or null if not found.
     */
    public static function BearerToken (): ?string
    {
        self::getAuthorizationHeader();

        // Check if the Authorization header is set and starts with "Bearer"
        if (self::$authorizationHeader && strpos(self::$authorizationHeader, 'Bearer ') === 0)
        {
            // Extract the token by removing "Bearer " from the header
            return substr(self::$authorizationHeader, 7);
        }

        // Return null if no Bearer token is found
        return null;
    }

    /**
     * Get API Key from the Authorization header.
     *
     * This method checks the `Authorization` header for the API Key authentication scheme
     * and returns the key if found.
     *
     * @return ?string Returns the API key as a string, or null if not found.
     */
    public static function ApiKey (): ?string
    {
        self::getAuthorizationHeader();

        // Check if the Authorization header is set and starts with "Api-Key"
        if (self::$authorizationHeader && strpos(self::$authorizationHeader, 'Api-Key ') === 0)
        {
            // Extract the API key by removing "Api-Key " from the header
            return substr(self::$authorizationHeader, 8);
        }

        // Return null if no API key is found
        return null;
    }
}