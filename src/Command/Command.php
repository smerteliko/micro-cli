<?php

namespace Smerteliko\MicroCli\Command;

use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;

abstract class Command
{
	public const ARG_REQUIRED = 1;
	public const ARG_OPTIONAL = 2;

	protected string $name = '';
	protected string $description = '';

	/** @var array<string, array> */
	protected array $arguments = [];

	/** @var array<string, array> */
	protected array $options = [];

	public function __construct()
	{
		$this->configure();
	}

	abstract protected function configure(): void;

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
	 * Регистрация ожидаемого аргумента
	 */
	protected function addArgument(string $name, string $description = '', mixed $default = null): self
	{
		$this->arguments[$name] = [
			'description' => $description,
			'default' => $default,
		];
		return $this;
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
}