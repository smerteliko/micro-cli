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

        $io->text('<comment>Options:</comment>');
        $globalOptions = [
            '-h, --help'    => 'Display help for the given command',
            '-q, --quiet'   => 'Do not output any message',
            '-v, --verbose' => 'Increase the verbosity of messages',
        ];

        $maxOptWidth = 0;
        foreach (array_keys($globalOptions) as $optName) {
            $maxOptWidth = max($maxOptWidth, strlen($optName));
        }

        foreach ($globalOptions as $optName => $desc) {
            $output->writeln("  <info>" . str_pad($optName, $maxOptWidth + 2) . "</info>{$desc}");
        }
        $io->newLine();

        $io->text('<comment>Available commands:</comment>');

        $commands = $this->getApplication()->all();

        // 1. Группируем команды
        $grouped = [];
        $maxWidth = 0;

        foreach ($commands as $name => $command) {
            if ($command->isHidden()) {
                continue;
            }
            $parts = explode(':', $name);
            $namespace = count($parts) > 1 ? $parts[0] : '';

            $grouped[$namespace][$name] = $command;
            $maxWidth = max($maxWidth, strlen($name));

        }

        ksort($grouped);

        foreach ($grouped as $namespace => $cmds) {
            if ($namespace !== '') {
                $io->text(" <comment>{$namespace}</comment>");
            }

            ksort($cmds);

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