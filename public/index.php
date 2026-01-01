<?php

declare(strict_types=1);

/**
 * TudásBázis - Single Entry Point
 * Minden HTTP kérés ide fut be
 */

// Hibajelentés fejlesztési módban
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Autoloader betöltése
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Környezeti változók betöltése
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

// DI Container betöltése
$container = require dirname(__DIR__) . '/config/container.php';

// Alkalmazás indítása
$app = $container->get(\App\Core\App::class);
$app->run();
