<?php

use Dotenv\Dotenv;
use PhpSlides\Foundation\Application;

$dotenv = Dotenv::createUnsafeImmutable(Application::$basePath);
$dotenv->load();
