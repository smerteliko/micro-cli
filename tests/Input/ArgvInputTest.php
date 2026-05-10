<?php

namespace Tests\Input;

use PHPUnit\Framework\TestCase;
use Smerteliko\MicroCli\Input\ArgvInput;
use Smerteliko\MicroCli\Command\Command;
use RuntimeException;

class ArgvInputTest extends TestCase
{
	public function testParsesCommandName(): void
	{
		$input = new ArgvInput(['console', 'test:run']);
		$this->assertSame('test:run', $input->getCommandName());
	}

	public function testParsesOptionsAndArguments(): void
	{
		$input = new ArgvInput(['console', 'test:run', 'arg1', '--force', '-y', '--name=John']);

		// Check if raw data is parsed correctly before binding
		$this->assertTrue($input->hasOption('force'));
		$this->assertTrue($input->hasOption('y'));
		$this->assertSame('John', $input->getOption('name'));
	}

	public function testBindsAndValidatesDefinitions(): void
	{
		$input = new ArgvInput(['console', 'test:run', 'Smerteliko']);

		$argumentsDef = [
			'name' => ['mode' => Command::ARG_REQUIRED, 'default' => null]
		];
		$optionsDef = [
			'yell' => ['default' => false]
		];

		$input->bind($argumentsDef, $optionsDef);

		// This should not throw an exception because 'name' is provided
		$input->validate($argumentsDef);

		$this->assertSame('Smerteliko', $input->getArgument('name'));
		$this->assertFalse($input->getOption('yell')); // Default value applied
	}

	public function testValidationThrowsExceptionOnMissingRequiredArgument(): void
	{
		$input = new ArgvInput(['console', 'test:run']);

		$argumentsDef = [
			'name' => ['mode' => Command::ARG_REQUIRED, 'default' => null]
		];

		$input->bind($argumentsDef, []);

		$this->expectException(RuntimeException::class);
		$this->expectExceptionMessage('Not enough arguments (missing: "name")');

		$input->validate($argumentsDef);
	}
}