<?php

namespace Smerteliko\MicroCli\Command\System;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;
use Smerteliko\MicroCli\Style\Style;

#[AsConsoleCommand(name: 'list', description: 'Lists all available commands')]
class ListCommand extends Command
{
	public function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new Style($input, $output);

		$io->title('Micro CLI Framework');
		$io->text('Usage:');
		$io->text('  command [options] [arguments]');
		$io->newLine();

		$io->text('<comment>Available commands:</comment>');

		// Fetch all registered commands from the Application
		$commands = $this->getApplication()->all();
		ksort($commands); // Sort alphabetically by name

		foreach ($commands as $name => $command) {
			// Read description either from property or Attribute (we set it in Command::parseAttributes)
			$description = $command->getDescription() ?: 'No description provided.';

			// Format output: align command names to the left (25 chars width)
			$output->writeln(sprintf("  <info>%-25s</info> %s", $name, $description));
		}

		$io->newLine();

		return 0;
	}
}