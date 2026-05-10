<?php

namespace Smerteliko\MicroCli\Command\System;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Attributes\Argument;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;
use Smerteliko\MicroCli\Style\Style;

#[AsConsoleCommand(name: 'help', description: 'Displays help for a command')]
class HelpCommand extends Command
{
	#[Argument(description: 'The command name', required: false, default: 'help')]
	public string $command_name;

	public function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new Style($input, $output);

		$command = $this->getApplication()->all()[$this->command_name] ?? null;

		if (!$command) {
			$io->error("Command '{$this->command_name}' not found.");
			return 1;
		}

		$io->title("Help: {$command->getName()}");
		if ($command->getDescription()) {
			$io->text("<comment>Description:</comment> " . $command->getDescription());
			$io->newLine();
		}

		$io->text("<comment>Usage:</comment>");
		$io->text("  {$command->getName()} [options] [arguments]");
		$io->newLine();

		$arguments = $command->getRegisteredArguments();
		if (!empty($arguments)) {
			$io->text("<comment>Arguments:</comment>");
			foreach ($arguments as $name => $meta) {
				$req = $meta['mode'] === Command::ARG_REQUIRED ? 'required' : 'optional';
				$default = $meta['default'] !== null ? " [default: {$meta['default']}]" : '';
				$output->writeln(sprintf("  <info>%-20s</info> %s (%s)%s", $name, $meta['description'], $req, $default));
			}
			$io->newLine();
		}

		$options = $command->getRegisteredOptions();
		if (!empty($options)) {
			$io->text("<comment>Options:</comment>");
			foreach ($options as $name => $meta) {
				$default = $meta['default'] !== false ? " [default: {$meta['default']}]" : '';
				$output->writeln(sprintf("  <info>--%-18s</info> %s%s", $name, $meta['description'], $default));
			}
			$io->newLine();
		}

		return 0;
	}
}