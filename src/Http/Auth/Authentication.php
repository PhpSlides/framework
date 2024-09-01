<?php declare(strict_types=1);

namespace PhpSlides\Http\Auth;

trait Authentication
{
	private static $authorizationHeader;

	private static function getAuthorizationHeader ()
	{
		$headers = getallheaders();
		self::$authorizationHeader = isset($headers['Authorization'])
		 ? $headers['Authorization']
		 : null;
	}

	/**
	 * Get Basic Authentication Credentials
	 */
	public static function BasicAuthCredentials (): ?array
	{
		self::getAuthorizationHeader();

		if (
		self::$authorizationHeader &&
		strpos(self::$authorizationHeader, 'Basic ') === 0
		)
		{
			$base64Credentials = substr(self::$authorizationHeader, 6);
			$decodedCredentials = base64_decode($base64Credentials);

			[ $username, $password ] = explode(':', $decodedCredentials, 2);
			return [ 'username' => trim(htmlspecialchars($username)), 'password' => trim(htmlspecialchars($password)) ];
		}
		return null;
	}

	/**
	 * Get Bearer Token Authentication
	 */
	public static function BearerToken (): ?string
	{
		self::getAuthorizationHeader();

		if (
		self::$authorizationHeader &&
		strpos(self::$authorizationHeader, 'Bearer ') === 0
		)
		{
			$token = substr(self::$authorizationHeader, 7);
			return $token;
		}
		return null;
	}
}