<?php

namespace Smerteliko\MicroCli\Discovery;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use ReflectionClass;

class CommandDiscoverer
{
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

					// If class has our Attribute, it's a command!
					if (!empty($attributes)) {
						$commands[] = new $fullClassName();
					}
				}
			}
		}

		return $commands;
	}
}