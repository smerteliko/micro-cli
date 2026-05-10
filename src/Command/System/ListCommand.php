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

		$io->text('<comment>Usage:</comment>');
		$io->text('  command [options] [arguments]');
		$io->newLine();

		// --- НОВЫЙ БЛОК: Глобальные опции ---
		$io->text('<comment>Options:</comment>');
		$globalOptions = [
			'-h, --help'    => 'Display help for the given command',
			'-q, --quiet'   => 'Do not output any message',
			'-v, --verbose' => 'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output',
		];

		// Находим самую длинную опцию для выравнивания
		$maxOptWidth = 0;
		foreach (array_keys($globalOptions) as $optName) {
			if (strlen($optName) > $maxOptWidth) {
				$maxOptWidth = strlen($optName);
			}
		}

		foreach ($globalOptions as $optName => $desc) {
			$paddedOpt = str_pad($optName, $maxOptWidth + 2);
			$output->writeln("  <info>{$paddedOpt}</info>{$desc}");
		}
		$io->newLine();
		// ------------------------------------

		$io->text('<comment>Available commands:</comment>');

		$commands = $this->getApplication()->all();
		ksort($commands);

		$namespaces = [];
		$maxWidth = 0;

		// Группировка и подсчет длины команд
		foreach ($commands as $name => $command) {
			$parts = explode(':', $name);
			$namespace = count($parts) > 1 ? $parts[0] : '_global';

			$namespaces[$namespace][$name] = $command;

			if (strlen($name) > $maxWidth) {
				$maxWidth = strlen($name);
			}
		}

		// Рендеринг команд "лесенкой"
		foreach ($namespaces as $namespace => $cmds) {
			if ($namespace !== '_global') {
				$io->text(" <comment>{$namespace}</comment>");
			}

			foreach ($cmds as $name => $command) {
				$description = $command->getDescription() ?: '';
				$paddedName = str_pad($name, $maxWidth + 2);

				$output->writeln("    <info>{$paddedName}</info>{$description}");
			}
		}

		$io->newLine();

		return 0;
	}
}