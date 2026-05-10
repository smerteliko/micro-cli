<?php
namespace Smerteliko\MicroCli\Output;

interface OutputInterface {
    public const VERBOSITY_QUIET = 16;
    public const VERBOSITY_NORMAL = 32;
    public const VERBOSITY_VERBOSE = 64;

    public function write(string $message, int $options = self::VERBOSITY_NORMAL): void;
    public function writeln(string $message, int $options = self::VERBOSITY_NORMAL): void;

    public function setVerbosity(int $level): void;
    public function getVerbosity(): int;

    public function isQuiet(): bool;
    public function isVerbose(): bool;
}