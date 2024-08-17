<?php

namespace PhpSlides\Services;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpSlides\Loader\FileLoader;

class JwtService
{
	private static $secretKey;
	private static $algorithm;

	public static function setup()
	{
		$jwt = (new FileLoader())
			->load(__DIR__ . '/../Config/jwt.config.php')
			->getLoad();

		self::$secretKey = $jwt['secret_key'];
		self::$algorithm = $jwt['algorithm'];
	}

	public static function encode(array $payload): string
	{
		self::setup();
		return JWT::encode($payload, self::$secretKey, self::$algorithm);
	}

	public static function decode(string $token): object
	{
		self::setup();
		return JWT::decode($token, new Key(self::$secretKey, self::$algorithm));
	}

	public static function verify(string $token): bool|array
	{
		try {
			return self::decode($token);
		} catch (Exception $e) {
			return false;
		}
	}
}

// Example payload
$payload = [
	'iss' => 'http://your-domain.com', // Issuer
	'iat' => time(), // Issued at
	'exp' => time() + 3600, // Expiration time
	'data' => 123 // Subject (user ID)
];
