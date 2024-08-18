<?php

use Dotenv\Dotenv;
use PhpSlides\Foundation\Application;

Dotenv::createUnsafeMutable(Application::$basePath)->load();

// Get the APP_ENV value
$appEnv = getenv('APP_ENV') ?: 'production';

Dotenv::createUnsafeMutable(
	Application::$basePath,
	'.env.' . $appEnv
)->safeLoad();
