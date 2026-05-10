<?php

namespace Smerteliko\MicroCli\Input;

interface InputInterface
{
	public function getArgument(string|int $name, mixed $default = null): mixed;

	public function getOption(string $name, mixed $default = null): mixed;

	public function hasOption(string $name): bool;

	/**
	 * Binds the raw input to the command's expected definitions.
	 *
	 * @param array<string, array> $argumentsDefinition
	 * @param array<string, array> $optionsDefinition
	 */
	public function bind(array $argumentsDefinition, array $optionsDefinition): void;

	/**
	 * Validates the input against the command's expected definitions.
	 *
	 * @param array<string, array> $argumentsDefinition
	 * @throws \RuntimeException When a required argument is missing
	 */
	public function validate(array $argumentsDefinition): void;
}