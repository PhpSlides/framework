<?php declare(strict_types=1);

namespace PhpSlides\Web;

use Firebase\JWT\Key;
use PhpSlides\Loader\FileLoader;
use Firebase\JWT\JWT as WebToken;
use PhpSlides\Interface\JwtService;

/**
 * The JwtService class provides methods for encoding, decoding, and verifying JSON Web Tokens (JWT).
 */
class JWT implements JwtService
{
	/**
	 * @var string $issuer The issuer of the JWT, typically the domain or application.
	 */
	private static $issuer;

	/**
	 * @var string $secretKey The secret key used for signing the JWT.
	 */
	private static $secretKey;

	/**
	 * @var string $algorithm The algorithm used to encode the JWT.
	 */
	private static $algorithm;

	/**
	 * Setup method to load JWT configurations from a configuration file.
	 * This method initializes the issuer, secret key, and algorithm.
	 *
	 * @return void
	 */
	private static function setup()
	{
		$jwt = (new FileLoader())
			->load(__DIR__ . '/../Config/jwt.config.php')
			->getLoad();

		self::$issuer = $jwt['issuer'];
		self::$algorithm = $jwt['algorithm'];
		self::$secretKey = $jwt['secret_key'];
	}

	/**
	 * Encode the provided payload into a JWT string.
	 *
	 * @param array $payload The payload to be encoded in the JWT.
	 * @return string The encoded JWT string.
	 */
	public static function encode(array $payload): string
	{
		self::setup();
		return WebToken::encode($payload, self::$secretKey, self::$algorithm);
	}

	/**
	 * Decode a JWT string into a PHP object.
	 *
	 * @param string $token The JWT string to decode.
	 * @param bool $parsed If true, removes standard claims like 'iss', 'iat', and 'exp' from the decoded object.
	 * @return object The decoded JWT as a PHP object.
	 */
	public static function decode(string $token, bool $parsed = true): object
	{
		self::setup();
		$decodedToken = WebToken::decode(
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

	/**
	 * Verify the validity of a JWT.
	 *
	 * @param string $token The JWT string to verify.
	 * @return bool Returns false if the token is invalid
	 */
	public static function verify(string $token): bool
	{
		try {
			$token = self::decode($token, false);
		} catch (\Exception $e) {
			return false;
		}

		if (self::$issuer !== $token->iss) {
			return false;
		}
		return true;
	}
}
