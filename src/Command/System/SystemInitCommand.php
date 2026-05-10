<?php

namespace Smerteliko\MicroCli\Command\System;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Style\Style;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;

#[AsConsoleCommand(name: 'system:init', description: 'Initializes a new Micro CLI project structure')]
class SystemInitCommand extends Command
{
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new Style($input, $output);
        $baseDir = getcwd();

        $io->title("Micro CLI: Project Initialization");

        $binDirName = $io->ask("Where should I put the executable script?", 'Console');

        $commandsDirName = $io->ask("Where should I put your command classes?", 'src/Commands');

        $io->newLine();

        $binPath = $baseDir . DIRECTORY_SEPARATOR . $binDirName;
        $commandsPath = $baseDir . DIRECTORY_SEPARATOR . $commandsDirName;
        $configPath = $baseDir . DIRECTORY_SEPARATOR . 'config';

        $dirs = [$binPath, $commandsPath, $configPath];

        foreach ($dirs as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
                $io->text("Created directory: <info>$dir</info>");
            }
        }

        $consoleFile = $binPath . DIRECTORY_SEPARATOR . 'console';

        if (!file_exists($consoleFile)) {
            $templatePath = __DIR__ . '/../../Resources/templates/console.tpl';

            if (!file_exists($templatePath)) {
                $io->error("Template file not found at $templatePath");
                return 1;
            }

            $content = file_get_contents($templatePath);

            $content = str_replace('src/Commands', $commandsDirName, $content);

            file_put_contents($consoleFile, $content);
            chmod($consoleFile, 0755); // Делаем файл исполняемым

            $io->text("Created executable: <info>{$binDirName}/console</info>");
        }

        $io->newLine();
        $io->success("Project initialized successfully!");
        $io->text("Try running: <comment>php {$binDirName}/console list</comment>");

        return 0;
    }
}