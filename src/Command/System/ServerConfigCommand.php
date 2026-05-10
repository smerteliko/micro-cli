<?php

namespace Smerteliko\MicroCli\Command\System;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Attributes\Argument;
use Smerteliko\MicroCli\Attributes\Option;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;
use Smerteliko\MicroCli\Style\Style;

#[AsConsoleCommand(name: 'server:config', description: 'View or edit server configuration files (php, nginx, apache)')]
class ServerConfigCommand extends Command
{
    #[Argument(description: 'Service name (php, nginx, apache)', required: true)]
    public string $service;

    #[Option(shortcut: 'e', description: 'Edit the file in terminal instead of viewing', default: false)]
    public bool $edit = false;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new Style($input, $output);
        $file = $this->resolveConfigFile($this->service);

        if (!$file) {
            $io->error("Could not locate configuration file for '{$this->service}'.");
            return 1;
        }

        if (!file_exists($file)) {
            $io->error("File does not exist: {$file}");
            return 1;
        }

        if ($this->edit) {
            // Check if user has permission
            if (!is_writable($file) && !str_starts_with(shell_exec('whoami'), 'root')) {
                $io->warning("You might need sudo privileges to edit {$file}.");
                if (!$io->confirm("Try to open anyway?", true)) {
                    return 0;
                }
            }

            // Open in default editor (fallback to nano)
            $editor = getenv('EDITOR') ?: 'nano';
            $io->info("Opening {$file} in {$editor}...");

            // passthru passes raw input/output streams directly to the child process
            passthru(sprintf('%s %s > `tty` < `tty`', escapeshellcmd($editor), escapeshellarg($file)), $status);

            if ($status === 0) {
                $io->success("Finished editing {$file}.");
            }
        } else {
            $io->title("Configuration: {$file}");
            // Use cat to output the file
            passthru(sprintf('cat %s', escapeshellarg($file)));
            $io->newLine();
        }

        return 0;
    }

    private function resolveConfigFile(string $service): ?string
    {
        return match (strtolower($service)) {
            // PHP has a built-in function to find its own loaded ini file!
            'php' => php_ini_loaded_file(),

            // For Nginx and Apache we check common Linux paths
            'nginx' => $this->findFirstExisting([
                                                    '/etc/nginx/nginx.conf',
                                                    '/usr/local/nginx/conf/nginx.conf',
                                                    '/opt/homebrew/etc/nginx/nginx.conf', // macOS
                                                ]),

            'apache' => $this->findFirstExisting([
                                                     '/etc/apache2/apache2.conf',
                                                     '/etc/httpd/conf/httpd.conf',
                                                     '/usr/local/apache2/conf/httpd.conf',
                                                 ]),

            default => null,
        };
    }

    private function findFirstExisting(array $paths): ?string
    {
        foreach ($paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        return null;
    }
}