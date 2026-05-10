<?php

namespace Smerteliko\MicroCli\Helper;

use Smerteliko\MicroCli\Output\OutputInterface;
use RuntimeException;

class ProcessHelper
{
    private OutputInterface $output;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function run(string|array $command): int
    {
        $cmdString = is_array($command)
            ? implode(' ', array_map('escapeshellarg', $command))
            : $command;

        if ($this->output->isVerbose()) {
            $this->output->writeln("<comment> > Executing: {$cmdString}</comment>");
        }

        $descriptorSpec = [
            0 => ["pipe", "r"],  // stdin
            1 => ["pipe", "w"],  // stdout
            2 => ["pipe", "w"],  // stderr
        ];

        $process = proc_open($cmdString, $descriptorSpec, $pipes);

        if (!is_resource($process)) {
            throw new RuntimeException("Failed to execute command: {$cmdString}");
        }

        fclose($pipes[0]);

        while (!feof($pipes[1]) || !feof($pipes[2])) {
            $out = fgets($pipes[1]);
            if ($out !== false) {
                $this->output->write($out);
            }

            $err = fgets($pipes[2]);
            if ($err !== false) {
                $this->output->write("<error>{$err}</error>");
            }
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        return proc_close($process);
    }
}