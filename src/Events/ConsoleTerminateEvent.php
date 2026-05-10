<?php

namespace Smerteliko\MicroCli\Events;

use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;

class ConsoleTerminateEvent
{
    public function __construct(
        public Command $command,
        public InputInterface $input,
        public OutputInterface $output,
        public int $exitCode
    ) {
    }

    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}