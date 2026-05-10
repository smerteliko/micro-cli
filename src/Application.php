<?php

namespace Smerteliko\MicroCli;

use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\ArgvInput;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\ConsoleOutput;
use Smerteliko\MicroCli\Output\OutputInterface;
use Throwable;

class Application
{
	/** @var array<string, Command> */
	private array $commands = [];

	public function add(Command $command): void
	{
		$command->setApplication($this); // Инжектим приложение в команду
		$this->commands[$command->getName()] = $command;
	}

	public function all(): array
	{
		return $this->commands;
	}

	public function run(?InputInterface $input = null, ?OutputInterface $output = null): int
	{
		$input = $input ?? new ArgvInput();
		$output = $output ?? new ConsoleOutput();

		$commandName = $input->getCommandName();

		if (!$commandName) {
			$output->writeln("<error>Error: Command name is not specified.</error>");
			$this->printHelp($output);
			return 1;
		}

		if (!isset($this->commands[$commandName])) {
			$output->writeln("<error>Error: Command '{$commandName}' not found.</error>");
			$this->printHelp($output);
			return 1;
		}

		try {
			$command = $this->commands[$commandName];

			$input->bind(
				$command->getRegisteredArguments(),
				$command->getRegisteredOptions()
			);

			// Validate input before executing
			$input->validate($command->getRegisteredArguments());

			return $command->execute($input, $output);
		} catch (Throwable $e) {
			$output->writeln("<error>Critical Error: {$e->getMessage()}</error>");
			return 1;
		}
	}

	private function printHelp(OutputInterface $output): void
	{
		$output->writeln("<comment>Available commands:</comment>");
		foreach ($this->commands as $name => $command) {
			$output->writeln("  <info>{$name}</info> - {$command->getDescription()}");
		}
	}
	public function loadCommandsFromDirectory(string $directory, string $namespace): void
	{
		if (!is_dir($directory)) {
			return;
		}

		// Ищем все PHP файлы в директории
		foreach (glob($directory . '/*.php') as $file) {
			// Получаем имя класса из имени файла (например, GreetCommand)
			$className = basename($file, '.php');
			// Собираем полное имя класса с неймспейсом (FQN)
			$fullClassName = $namespace . '\\' . $className;

			// Проверяем, существует ли класс и наследуется ли он от нашей базовой команды
			if (class_exists($fullClassName) && is_subclass_of($fullClassName, Command::class)) {
				$reflection = new \ReflectionClass($fullClassName);

				// Убеждаемся, что класс можно инстанцировать (не абстрактный)
				if (!$reflection->isAbstract()) {
					$this->add(new $fullClassName());
				}
			}
		}
	}
}