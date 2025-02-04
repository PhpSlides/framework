<?php

use function payload;
use PhpSlides\Core\Web\JWT;

include_once __DIR__ . '/../autoload.php';
include_once __DIR__ . '/../../src/Globals/Functions.php';

/**
 * Testing and creating new PayLoad with the `payload` function
 */
$payload = payload(data: ['user_id' => '555'], expires: '+7 days');

/**
 * Testing JwtService encode method
 */
$token = JWT::encode($payload);

/**
 * Testing JwtService verify token method
 */
$verifyToken = JWT::verify($token);

if ($verifyToken) {
	/**
	 * Testing JwtService decode method
	 */
	$decodedToken = JWT::decode($token);
	print_r($decodedToken);
}
