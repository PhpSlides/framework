<?php

use Dotenv\Dotenv;
use PhpSlides\Foundation\Application;

try {
Dotenv::createUnsafeMutable(Application::$basePath)->load();
} catch (Exception $e) {
   exit($e->getMessage());
}

// Get the APP_ENV value
$appEnv = getenv('APP_ENV') ?: 'production';

Dotenv::createUnsafeMutable(
	Application::$basePath,
	'.env.' . $appEnv
)->safeLoad();
