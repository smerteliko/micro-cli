<?php

namespace Smerteliko\MicroCli\Testing;

use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\ArgvInput;
use Smerteliko\MicroCli\Output\BufferedOutput;

class CommandTester
{
	private Command $command;
	private BufferedOutput $output;
	private int $statusCode = 0;

	public function __construct(Command $command)
	{
		$this->command = $command;
		$this->output = new BufferedOutput();
	}

	public function execute(array $inputArgs = []): int
	{
		$argv = array_merge(['console', $this->command->getName()], $inputArgs);

		$input = new ArgvInput($argv);

		$input->bind(
			$this->command->getRegisteredArguments(),
			$this->command->getRegisteredOptions()
		);

		if (method_exists($input, 'validate')) {
			$input->validate($this->command->getRegisteredArguments());
		}

		$this->command->bindProperties($input);

		$this->statusCode = $this->command->execute($input, $this->output);

		return $this->statusCode;
	}

	public function getDisplay(): string
	{
		return $this->output->fetch();
	}

	public function getStatusCode(): int
	{
		return $this->statusCode;
	}
}