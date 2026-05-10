<?php

namespace Smerteliko\MicroCli\Output;

class ConsoleOutput implements OutputInterface
{

	private const COLORS = [
		'<info>'  => "\033[32m",
		'</info>' => "\033[0m",
		'<error>' => "\033[31m",
		'</error>'=> "\033[0m",
		'<comment>'=> "\033[33m",
		'</comment>'=> "\033[0m",
	];

	public function write(string $message): void
	{
		echo $this->format($message);
	}

	public function writeln(string $message): void
	{
		echo $this->format($message) . PHP_EOL;
	}

	private function format(string $message): string
	{
		return str_replace(array_keys(self::COLORS), array_values(self::COLORS), $message);
	}
}