<?php declare(strict_types=1);

namespace PhpSlides\Interface;

/**
 * Interface JwtServiceInterface
 *
 * Defines the contract for JWT operations, including encoding, decoding, and verifying tokens.
 */
interface JwtServiceInterface
{
	/**
	 * Encode the provided payload into a JWT string.
	 *
	 * @param array $payload The payload to be encoded in the JWT.
	 * @return string The encoded JWT string.
	 */
	public static function encode(array $payload): string;

	/**
	 * Decode a JWT string into a PHP object.
	 *
	 * @param string $token The JWT string to decode.
	 * @param bool $parsed If true, removes standard claims like 'iss', 'iat', and 'exp' from the decoded object.
	 * @return object The decoded JWT as a PHP object.
	 */
	public static function decode(string $token, bool $parsed = true): object;

	/**
	 * Verify the validity of a JWT.
	 *
	 * @param string $token The JWT string to verify.
	 * @return bool|array Returns false if the token is invalid, or the decoded token array if valid.
	 */
	public static function verify(string $token): bool|array;
}
