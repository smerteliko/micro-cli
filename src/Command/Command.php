<?php

namespace Smerteliko\MicroCli\Command;

use ReflectionClass;
use Smerteliko\MicroCli\Application;
use Smerteliko\MicroCli\Attributes\Argument;
use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Attributes\Option;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;

abstract class Command
{
	public const ARG_REQUIRED = 1;
	public const ARG_OPTIONAL = 2;

	protected string $name = '';
	protected string $description = '';
	protected array $arguments = [];
	protected array $options = [];

	public function __construct()
	{
		$this->parseAttributes();
		$this->configure();
	}

	/**
	 * Parses PHP 8 Attributes to auto-configure the command
	 */
	private function parseAttributes(): void
	{
		$reflection = new ReflectionClass($this);

		// 1. Parse Class Attribute (Name and Description)
		$classAttributes = $reflection->getAttributes(AsConsoleCommand::class);
		if (!empty($classAttributes)) {
			/** @var AsConsoleCommand $commandAttribute */
			$commandAttribute = $classAttributes[0]->newInstance();
			$this->name = $commandAttribute->name;
			$this->description = $commandAttribute->description;
		}

		// 2. Parse Property Attributes (Arguments and Options)
		foreach ($reflection->getProperties() as $property) {
			$argAttributes = $property->getAttributes(Argument::class);
			if (!empty($argAttributes)) {
				/** @var Argument $arg */
				$arg = $argAttributes[0]->newInstance();
				$mode = $arg->required ? self::ARG_REQUIRED : self::ARG_OPTIONAL;
				$this->addArgument($property->getName(), $mode, $arg->description, $arg->default);
			}

			$optAttributes = $property->getAttributes(Option::class);
			if (!empty($optAttributes)) {
				/** @var Option $opt */
				$opt = $optAttributes[0]->newInstance();
				$this->addOption($property->getName(), $opt->description, $opt->default);
			}
		}
	}

	/**
	 * Automatically binds input values to class properties based on Attributes
	 */
	public function bindProperties(InputInterface $input): void
	{
		$reflection = new ReflectionClass($this);

		foreach ($reflection->getProperties() as $property) {
			$name = $property->getName();

			if (!empty($property->getAttributes(Argument::class))) {
				$property->setValue($this, $input->getArgument($name));
			}

			if (!empty($property->getAttributes(Option::class))) {
				$property->setValue($this, $input->getOption($name));
			}
		}
	}

	// Optional manual configuration method
	protected function configure(): void
	{
	}


	abstract public function execute(InputInterface $input, OutputInterface $output): int;

	protected function setName(string $name): self
	{
		$this->name = $name;
		return $this;
	}

	public function getName(): string
	{
		return $this->name;
	}

	protected function setDescription(string $description): self
	{
		$this->description = $description;
		return $this;
	}

	public function getDescription(): string
	{
		return $this->description;
	}


	/**
	 * Регистрация ожидаемой опции (--option)
	 */
	protected function addOption(string $name, string $description = '', mixed $default = false): self
	{
		$this->options[$name] = [
			'description' => $description,
			'default' => $default,
		];
		return $this;
	}

	public function getRegisteredArguments(): array
	{
		return $this->arguments;
	}

	public function getRegisteredOptions(): array
	{
		return $this->options;
	}

	/**
	 * Registers an expected argument.
	 */
	protected function addArgument(string $name, int $mode = self::ARG_OPTIONAL, string $description = '', mixed $default = null): self
	{
		$this->arguments[$name] = [
			'mode' => $mode,
			'description' => $description,
			'default' => $default,
		];
		return $this;
	}

	public function setApplication(Application $application): void
	{
		$this->application = $application;
	}

	public function getApplication(): ?Application
	{
		return $this->application;
	}

	protected function setSchedule(string $cronExpression): self
	{
		$this->schedule = $cronExpression;
		return $this;
	}

	public function getSchedule(): ?string
	{
		return $this->schedule;
	}

}