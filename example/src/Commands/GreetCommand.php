<?php

namespace App\Commands;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Attributes\Argument;
use Smerteliko\MicroCli\Attributes\Option;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;

#[AsConsoleCommand(name: 'greet', description: 'Advanced greeting command for testing all input cases')]
class GreetCommand extends Command
{
    #[Argument(description: 'Who do you want to greet?', required: true)]
    protected ?string $name;

    #[Argument(description: 'Custom greeting word', required: false, default: 'Hello')]
    public string $word;

    #[Option(shortcut: 'y', description: 'Yell the output (uppercase)', default: false)]
    public bool $yell = false;

    #[Option(shortcut: 'c', description: 'Color tag (info, error, comment)', default: 'info')]
    public string $color;

    #[Option(shortcut: 'r', description: 'Number of times to repeat the greeting', default: 1)]
    public int $repeat;

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $text = "{$this->word}, {$this->name}!";

        if ($this->yell) {
            $text = strtoupper($text);
        }

        $tag = in_array($this->color, ['info', 'error', 'comment']) ? $this->color : 'info';

        for ($i = 0; $i < $this->repeat; $i++) {
            $output->writeln("<{$tag}>{$text}</{$tag}>");
        }

        return 0;
    }
}