<?php

namespace PhpSlides\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpSlides\Loader\FileLoader;

class JwtService
{
	private static $issuer;
	private static $secretKey;
	private static $algorithm;

	private static function setup()
	{
		$jwt = (new FileLoader())
			->load(__DIR__ . '/../Config/jwt.config.php')
			->getLoad();

		self::$issuer = $jwt['issuer'];
		self::$secretKey = $jwt['secret_key'];
		self::$algorithm = $jwt['algorithm'];
	}

	public static function encode(array $payload): string
	{
		self::setup();
		return JWT::encode($payload, self::$secretKey, self::$algorithm);
	}

	public static function decode(string $token, bool $parsed = true): object
	{
		self::setup();
		$decodedToken = JWT::decode(
			$token,
			new Key(self::$secretKey, self::$algorithm)
		);

		if ($parsed === true) {
			unset($decodedToken->iss);
			unset($decodedToken->iat);
			unset($decodedToken->exp);
		}
		return $decodedToken;
	}

	public static function verify(string $token): bool|array
	{
		try {
			$token = self::decode($token, false);
		} catch (\Exception $e) {
			return false;
		}

		if (
			$token->iss !== self::$issuer ||
			$token->iat > time() ||
			$token->exp < time()
		) {
			return false;
		}
		return true;
	}
}