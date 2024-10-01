<?php

use Dotenv\Dotenv;

try {
	Dotenv::createUnsafeMutable('app/../')->load();
} catch (Exception $e) {
	exit($e->getMessage());
}

// Get the APP_ENV value
$appEnv = getenv('APP_ENV') ?: 'production';

Dotenv::createUnsafeMutable('app/../', '.env.' . $appEnv)->safeLoad();
