<?php

namespace Smerteliko\MicroCli\Events;

use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;

class ConsoleCommandEvent
{
    private bool $commandShouldRun = true;

    public function __construct(
        public Command $command,
        public InputInterface $input,
        public OutputInterface $output
    ) {
    }

    public function disableCommand(): void
    {
        $this->commandShouldRun = false;
    }

    public function commandShouldRun(): bool
    {
        return $this->commandShouldRun;
    }
}