<?php

namespace Smerteliko\MicroCli\Input;

use RuntimeException;

class ArgvInput implements InputInterface {
    private ?string $commandName    = NULL;
    private array   $arguments      = [];
    private array   $options        = [];
    private array   $boundArguments = [];

    public function __construct(?array $argv = NULL) {
        $argv = $argv ?? $_SERVER['argv'];
        $this->parse($argv);
    }

    private function parse(array $argv): void {
        array_shift($argv);

        if (isset($argv[0]) && !str_starts_with($argv[0], '-')) {
            $this->commandName = array_shift($argv);
        }

        foreach ($argv as $token) {
            if (str_starts_with($token, '--')) {
                $parts                      = explode('=',
                                                      substr($token, 2),
                                                      2);
                $this->options[ $parts[0] ] = $parts[1] ?? TRUE;
            } elseif (str_starts_with($token, '-')) {
                $this->options[ substr($token, 1) ] = TRUE;
            } else {
                $this->arguments[] = $token;
            }
        }
    }

    public function bind(array $argumentsDefinition,
                         array $optionsDefinition): void {
        $position = 0;

        // Bind arguments by position to their defined names
        foreach ($argumentsDefinition as $name => $meta) {
            $this->boundArguments[ $name ] = $this->arguments[ $position ] ?? $meta['default'];
            $position++;
        }

        // Apply default values for missing options
        foreach ($optionsDefinition as $name => $meta) {
            if (!isset($this->options[ $name ])) {
                $this->options[ $name ] = $meta['default'];
            }
        }
    }

    public function getCommandName(): ?string {
        return $this->commandName;
    }

    public function getArgument(string|int $name,
                                mixed      $default = NULL): mixed {
        // Look in bound arguments first, fallback to raw positional arguments
        return $this->boundArguments[ $name ] ?? $this->arguments[ $name ] ?? $default;
    }

    public function getOption(string $name, mixed $default = NULL): mixed {
        return $this->options[ $name ] ?? $default;
    }

    public function hasOption(string $name): bool {
        return array_key_exists($name,
                                $this->options) && $this->options[ $name ] !== FALSE;
    }

    public function setArgument(string|int $name, mixed $value): void
    {
        $this->boundArguments[$name] = $value;
    }

    public function validate(array $argumentsDefinition): void {
        $missingArguments = [];

        foreach ($argumentsDefinition as $name => $meta) {
            $isRequired = ( $meta['mode'] === \Smerteliko\MicroCli\Command\Command::ARG_REQUIRED );
            $value      = $this->getArgument($name);

            if ($isRequired && $value === NULL) {
                $missingArguments[] = $name;
            }
        }

        if (count($missingArguments) > 0) {
            throw new RuntimeException(sprintf('Not enough arguments (missing: "%s").',
                                               implode(', ',
                                                       $missingArguments)));
        }
    }
}