<?php

namespace App\Commands;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Attributes\Argument;
use Smerteliko\MicroCli\Attributes\Option;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;
use Smerteliko\MicroCli\Style\Style;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

#[AsConsoleCommand(name: 'utils:tabs-to-spaces', description: 'Recursively converts tabs to spaces in your project files')]
class TabsToSpacesCommand extends Command
{
    #[Argument(description: 'Directory to scan', default: '.')]
    public string $directory;

    #[Option(shortcut: 'i', description: 'Number of spaces per tab', default: 4)]
    public int $indent;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new Style($input, $output);
        $targetDir = realpath($this->directory);

        if (!$targetDir || !is_dir($targetDir)) {
            $io->error("Directory not found: {$this->directory}");
            return 1;
        }

        $io->title("Tabs to Spaces Converter");
        $io->text("Scanning: <info>{$targetDir}</info>");
        $io->text("Indentation: <info>{$this->indent} spaces</info>");

        $files = $this->findFiles($targetDir);
        $total = count($files);

        if ($total === 0) {
            $io->warning("No suitable files found to process.");
            return 0;
        }

        if (!$io->confirm("Found <comment>{$total}</comment> files. Proceed with conversion?", true)) {
            $io->warning("Operation cancelled.");
            return 0;
        }

        $progressBar = $io->createProgressBar($total);
        $progressBar->start();

        $spaces = str_repeat(' ', $this->indent);
        $changedCount = 0;

        foreach ($files as $file) {
            $content = file_get_contents($file);

            if (str_contains($content, "\t")) {
                $newContent = str_replace("\t", $spaces, $content);
                file_put_contents($file, $newContent);
                $changedCount++;
            }

            $progressBar->advance();
        }

        $progressBar->finish();

        $io->newLine();
        $io->success("Finished! Processed {$total} files. Updated <comment>{$changedCount}</comment> files.");

        return 0;
    }

    /**
     * Рекурсивно ищет текстовые файлы, пропуская бинарники и кэш.
     */
    private function findFiles(string $dir): array
    {
        $directory = new RecursiveDirectoryIterator($dir);
        $iterator = new RecursiveIteratorIterator($directory);

        $regex = new RegexIterator($iterator, '/\.(php|json|md|xml|env|txt|css|js)$/i', RegexIterator::GET_MATCH);

        $fileList = [];
        foreach ($regex as $path => $match) {
            if (str_contains($path, '/vendor/') || str_contains($path, '/.git/') || str_contains($path, '/var/cache/')) {
                continue;
            }

            if (is_file($path)) {
                $fileList[] = $path;
            }
        }

        return $fileList;
    }
}