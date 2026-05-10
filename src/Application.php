<?php

namespace Smerteliko\MicroCli;

use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Command\System\ListCommand;
use Smerteliko\MicroCli\Command\System\HelpCommand;
use Smerteliko\MicroCli\Command\System\ScheduleRunCommand;
use Smerteliko\MicroCli\Command\System\ServerConfigCommand;
use Smerteliko\MicroCli\Input\ArgvInput;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\ConsoleOutput;
use Smerteliko\MicroCli\Output\OutputInterface;
use Throwable;

class Application
{
	/** @var array<string, Command> */
	private array $commands = [];

	public function __construct(private ?\Smerteliko\MicroCli\Config\Config $config = null)
	{
		// Register default system commands
		$this->add(new ListCommand());
		$this->add(new ServerConfigCommand());
		$this->add(new HelpCommand());
		if ($this->config !== null) {
			$this->add(new ScheduleRunCommand($this->config));
		}
	}

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

		// Check for verbosity flag globally (-v)
		if ($input->hasOption('v') || $input->hasOption('verbose')) {
			$output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
		}

		$commandName = $input->getCommandName();

		// If no command, default to 'list'
		if (!$commandName) {
			$commandName = 'list';
		}

		// Global Help Interceptor
		if ($input->hasOption('help') || $input->hasOption('h')) {
			// Swap the command to 'help' and pass the original command as argument
			$input->setArgument('command_name', $commandName);
			$commandName = 'help';
		}

		if (!isset($this->commands[$commandName])) {
			$output->writeln("<error>Error: Command '{$commandName}' not found.</error>");
			return 1;
		}

		try {
			$command = $this->commands[$commandName];

			$input->bind(
				$command->getRegisteredArguments(),
				$command->getRegisteredOptions()
			);

			if (method_exists($input, 'validate')) {
				$input->validate($command->getRegisteredArguments());
			}

			$command->bindProperties($input);

			return $command->execute($input, $output);

		} catch (Throwable $e) {
			$this->renderThrowable($e, $output);
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

		foreach (glob($directory . '/*.php') as $file) {
			$className = basename($file, '.php');
			$fullClassName = $namespace . '\\' . $className;

			if (class_exists($fullClassName) && is_subclass_of($fullClassName, Command::class)) {
				$reflection = new \ReflectionClass($fullClassName);

				if (!$reflection->isAbstract()) {
					$this->add(new $fullClassName());
				}
			}
		}
	}


	/**
	 * Renders a beautiful exception output with Stack Trace if verbose
	 */
	private function renderThrowable(Throwable $e, OutputInterface $output): void
	{
		$output->writeln('');
		$output->writeln("<error>  [" . get_class($e) . "]  </error>");
		$output->writeln("<error>  " . $e->getMessage() . "  </error>");
		$output->writeln('');

		$output->writeln("<comment>In {$e->getFile()} on line {$e->getLine()}</comment>");

		if ($output->isVerbose()) {
			$output->writeln('');
			$output->writeln('<comment>Exception trace:</comment>');
			foreach ($e->getTrace() as $i => $trace) {
				$class = $trace['class'] ?? '';
				$type = $trace['type'] ?? '';
				$func = $trace['function'] ?? '';
				$file = $trace['file'] ?? 'n/a';
				$line = $trace['line'] ?? 'n/a';
				$output->writeln(" {$i}. {$class}{$type}{$func}() at <info>{$file}:{$line}</info>");
			}
		}

		$output->writeln('');
	}
}