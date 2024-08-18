<?php

use PhpSlides\Services\JwtService;

include_once __DIR__ . '/../autoload.php';
include_once __DIR__ . '/../../src/Globals/Functions.php';

/**
 * Testing and creating new PayLoad with the `payload` function
 */
$payload = payload(data: ['user_id' => '555'], expires: time() + 3600);

/**
 * Testing JwtService encode method
 */
$token = JwtService::encode($payload);

/**
 * Testing JwtService verify token method
 */
$verifyToken = JwtService::verify($token);

if ($verifyToken) {
	/**
	 * Testing JwtService decode method
	 */
	$decodedToken = JwtService::decode($token);
	print_r($decodedToken);
}
