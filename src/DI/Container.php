<?php

namespace Smerteliko\MicroCli\DI;

use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

class Container implements ContainerInterface
{
	/** @var array<string, object> */
	private array $instances = [];

	/**
	 * Register an existing object instance in the container.
	 */
	public function set(string $id, object $service): void
	{
		$this->instances[$id] = $service;
	}

	public function has(string $id): bool
	{
		return isset($this->instances[$id]) || class_exists($id);
	}

	/**
	 * Resolves a class and its dependencies.
	 *
	 * @throws \ReflectionException
	 */
	public function get(string $id): mixed
	{
		// 1. If we already have the instance, return it (Singleton behavior)
		if (isset($this->instances[$id])) {
			return $this->instances[$id];
		}

		// 2. If it's not a class we can instantiate, throw an error
		if (!class_exists($id)) {
			throw new RuntimeException("Service or class not found: {$id}");
		}

		// 3. Autowire the class
		$instance = $this->autowire($id);

		// 4. Cache the instance
		$this->instances[$id] = $instance;

		return $instance;
	}

	/**
	 * The core Autowiring logic using Reflection API.
	 *
	 * @throws \ReflectionException
	 */
	private function autowire(string $class): object
	{
		$reflection = new ReflectionClass($class);

		if (!$reflection->isInstantiable()) {
			throw new RuntimeException("Class {$class} is not instantiable.");
		}

		$constructor = $reflection->getConstructor();

		// If no constructor, just instantiate it
		if (!$constructor) {
			return new $class();
		}

		$parameters = $constructor->getParameters();
		$dependencies = [];

		foreach ($parameters as $parameter) {
			$type = $parameter->getType();

			// If it has no type or is a built-in PHP type (string, int, array)
			if (!$type instanceof ReflectionNamedType || $type->isBuiltin()) {
				// If it has a default value (e.g. string $name = 'default'), use it
				if ($parameter->isDefaultValueAvailable()) {
					$dependencies[] = $parameter->getDefaultValue();
					continue;
				}

				throw new RuntimeException("Cannot resolve parameter '{$parameter->getName()}' in class {$class}. Please provide a class type-hint or a default value.");
			}

			// Recursive call to resolve the dependency
			$dependencies[] = $this->get($type->getName());
		}

		// Instantiate the class with the resolved dependencies
		return $reflection->newInstanceArgs($dependencies);
	}
}