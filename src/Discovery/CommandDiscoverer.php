<?php

namespace Smerteliko\MicroCli\Discovery;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use ReflectionClass;
use Smerteliko\MicroCli\DI\ContainerInterface;
use SplFileInfo;

class CommandDiscoverer
{
    public function __construct(private readonly ContainerInterface $container)
    {
    }
    public function discover(string $directory, string $namespace): array
    {
        $commands = [];
        $directory = realpath($directory);

        if (!$directory || !is_dir($directory)) {
            return $commands;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory)
        );

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if ($file->isDir() || $file->getExtension() !== 'php') {
                continue;
            }
            $relativePath = str_replace($directory . DIRECTORY_SEPARATOR,
                                        '',
                                        $file->getRealPath());

            $classPath = str_replace([ DIRECTORY_SEPARATOR, '.php' ],
                                     [ '\\', '' ],
                                     $relativePath);
            $fullClassName = $namespace . '\\' . $classPath;

            if (class_exists($fullClassName)) {
                $reflection = new ReflectionClass($fullClassName);

                if (!$reflection->isAbstract()) {
                    $attributes = $reflection->getAttributes(AsConsoleCommand::class);

                    if (!empty($attributes)) {
                        $commands[] = $this->container->get($fullClassName);
                    }
                }
            }
        }

        return $commands;
    }
}