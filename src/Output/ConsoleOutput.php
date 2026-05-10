<?php

namespace Smerteliko\MicroCli\Output;

class ConsoleOutput implements OutputInterface
{

    private int $verbosity = self::VERBOSITY_NORMAL;

    private const COLORS = [
        '<info>'  => "\033[32m",
        '</info>' => "\033[0m",
        '<error>' => "\033[31m",
        '</error>'=> "\033[0m",
        '<comment>'=> "\033[33m",
        '</comment>'=> "\033[0m",
    ];

    public function write(string $message, int $options = self::VERBOSITY_NORMAL): void
    {
        if ($this->verbosity >= $options) {
            echo $this->format($message);
        }
    }

    public function writeln(string $message, int $options = self::VERBOSITY_NORMAL): void
    {
        if ($this->verbosity >= $options) {
            echo $this->format($message) . PHP_EOL;
        }
    }

    public function setVerbosity(int $level): void
    {
        $this->verbosity = $level;
    }

    public function getVerbosity(): int
    {
        return $this->verbosity;
    }

    public function isQuiet(): bool
    {
        return $this->verbosity === self::VERBOSITY_QUIET;
    }

    public function isVerbose(): bool
    {
        return $this->verbosity >= self::VERBOSITY_VERBOSE;
    }

    private function format(string $message): string
    {
        return str_replace(array_keys(self::COLORS), array_values(self::COLORS), $message);
    }
}