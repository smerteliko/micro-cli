#!/usr/bin/env php
<?php

/**
 * Micro CLI Framework Binary
 * This file is used to bootstrap projects and run system commands.
 */

$autoloadFiles = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php',
];

$autoloader = null;
foreach ($autoloadFiles as $file) {
    if (file_exists($file)) {
        $autoloader = require $file;
        break;
    }
}

if (!$autoloader) {
    die("Composer autoloader not found. Please run 'composer install'.\n");
}

use Smerteliko\MicroCli\Application;
use Smerteliko\MicroCli\DI\Container;

$container = new Container();
$app = new Application('Micro CLI Framework', '1.0.0', $container);


// Запускаем!
$app->run();