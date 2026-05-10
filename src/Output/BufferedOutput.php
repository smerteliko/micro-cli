<?php

namespace Smerteliko\MicroCli\Output;

class BufferedOutput implements OutputInterface
{
    private string $buffer = '';
    private int $verbosity = self::VERBOSITY_NORMAL;

    public function write(string $message, int $options = self::VERBOSITY_NORMAL): void
    {
        if ($this->verbosity >= $options) {
            $this->buffer .= $message;
        }
    }

    public function writeln(string $message, int $options = self::VERBOSITY_NORMAL): void
    {
        if ($this->verbosity >= $options) {
            $this->buffer .= $message . PHP_EOL;
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

    public function fetch(): string
    {
        $content = $this->buffer;
        $this->buffer = '';
        return $content;
    }
}