<?php

use Dotenv\Dotenv;
use PhpSlides\Foundation\Application;

$dotenv = Dotenv::createMutable(Application::$basePath);
$dotenv->safeLoad();

// Get the APP_ENV value
$appEnv = getenv('APP_ENV') ?: 'production';

// Load the environment-specific .env file if it exists
$envFilePath = Application::$basePath . '/.env.' . $appEnv;
if (file_exists($envFilePath)) {
    $dotenv->overload();
    $dotenv = Dotenv::createMutable(Application::$basePath, '.env.' . $appEnv);
    $dotenv->safeLoad();
}