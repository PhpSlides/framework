<?php declare(strict_types=1);

namespace PhpSlides\Src\Web;

use Firebase\JWT\Key;
use Firebase\JWT\JWT as WebToken;
use PhpSlides\Src\Loader\FileLoader;
use PhpSlides\Src\Interface\JwtService;

/**
 * The JwtService class provides methods for encoding, decoding, and verifying JSON Web Tokens (JWT).
 * It supports JWT operations with customizable configuration for issuer, secret key, and encoding algorithm.
 */
class JWT implements JwtService
{
	/**
	 * @var string $issuer The issuer of the JWT, typically the domain or application. This value is used to validate the token's source.
	 */
	private static $issuer;

	/**
	 * @var string $secretKey The secret key used for signing and verifying the JWT. This key is crucial for the security of the JWT.
	 */
	private static $secretKey;

	/**
	 * @var string $algorithm The algorithm used to encode and decode the JWT, typically 'HS256' or another HMAC or RSA-based algorithm.
	 */
	private static $algorithm;

	/**
	 * Setup method to load JWT configurations from a configuration file.
	 * This method initializes the issuer, secret key, and algorithm properties by loading them from a config file.
	 * The configuration file should contain 'issuer', 'secret_key', and 'algorithm' settings.
	 *
	 * @return void
	 */
	private static function setup(): void
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
	 * This method takes an array representing the payload and returns an encoded JWT.
	 *
	 * @param array $payload The payload to be encoded in the JWT. Typically includes user-specific data or claims.
	 * @return string The encoded JWT string.
	 *
	 * @throws \UnexpectedValueException If there is an error during encoding, such as an invalid key or algorithm.
	 */
	public static function encode(array $payload): string
	{
		self::setup();
		return WebToken::encode($payload, self::$secretKey, self::$algorithm);
	}

	/**
	 * Decode a JWT string into a PHP object.
	 * This method decodes a JWT and optionally removes standard claims like 'iss', 'iat', and 'exp' if requested.
	 *
	 * @param string $token The JWT string to decode. The token must be properly formatted and signed.
	 * @param bool $parsed If true, removes standard claims like 'iss', 'iat', and 'exp' from the decoded object.
	 *                      This is useful when you only care about custom claims.
	 * @return object The decoded JWT as a PHP object. Contains the claims and any custom data stored in the token.
	 *
	 * @throws \Exception If the token is invalid, expired, or cannot be decoded.
	 */
	public static function decode(string $token, bool $parsed = true): object
	{
		self::setup();
		$decodedToken = WebToken::decode(
			$token,
			new Key(self::$secretKey, self::$algorithm),
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
	 * This method checks if the JWT is valid by decoding the token and ensuring it matches the expected issuer.
	 *
	 * @param string $token The JWT string to verify. The token must be properly signed and formatted.
	 * @return bool Returns true if the token is valid and the issuer matches, otherwise returns false.
	 *
	 * @throws \Exception If the token cannot be decoded or if the issuer is invalid.
	 */
	public static function verify(string $token): bool
	{
		try {
			$token = self::decode($token, false);
		} catch (\Exception $e) {
			return false;
		}

		// Validate that the issuer matches the expected issuer
		if (self::$issuer !== $token->iss) {
			return false;
		}
		return true;
	}
}
