<?php

namespace Smerteliko\MicroCli\Discovery;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use ReflectionClass;
use Smerteliko\MicroCli\DI\ContainerInterface;

class CommandDiscoverer
{
	public function __construct(private readonly ContainerInterface $container)
	{
	}
	public function discover(string $directory, string $namespace): array
	{
		$commands = [];

		if (!is_dir($directory)) {
			return $commands;
		}

		foreach (glob($directory . '/*.php') as $file) {
			$className = basename($file, '.php');
			$fullClassName = $namespace . '\\' . $className;

			if (class_exists($fullClassName)) {
				$reflection = new ReflectionClass($fullClassName);

				if (!$reflection->isAbstract()) {
					$attributes = $reflection->getAttributes(AsConsoleCommand::class);

					if (!empty($attributes)) {
						// Magic! Let the container build the command with all its dependencies
						$commands[] = $this->container->get($fullClassName);
					}
				}
			}
		}

		return $commands;
	}
}