#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Smerteliko\MicroCli\Application;
use Smerteliko\MicroCli\DI\Container;
use Smerteliko\MicroCli\Discovery\CommandDiscoverer;

$container = new Container();
$discoverer = new CommandDiscoverer($container);

$app = new Application('Micro CLI App', '1.0.0', $container);

// Auto-discover commands in the project
$commands = $discoverer->discover(__DIR__ . '/../src/Commands', 'App\Commands');
$app->addCommands($commands);

$app->run();