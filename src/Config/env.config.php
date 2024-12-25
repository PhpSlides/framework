<?php

use Dotenv\Dotenv;
use PhpSlides\Core\Foundation\Application;

$basePath = Application::$basePath === '' ? 'app/../' : Application::$basePath;

try {
	Dotenv::createUnsafeMutable($basePath)->load();
} catch (Exception $e) {
	exit($e->getMessage());
}

// Get the APP_ENV value
$appEnv = getenv('APP_ENV') ?: 'production';

Dotenv::createUnsafeMutable($basePath, '.env.' . $appEnv)->safeLoad();
