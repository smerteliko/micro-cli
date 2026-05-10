<?php

namespace Tests\Commands;

use PHPUnit\Framework\TestCase;
use App\Commands\GreetCommand;
use Smerteliko\MicroCli\Testing\CommandTester;
use RuntimeException;

class GreetCommandTest extends TestCase
{
    private CommandTester $tester;

    protected function setUp(): void
    {
        parent::setUp();
        $command = new GreetCommand();
        $this->tester = new CommandTester($command);
    }

    public function testExecuteWithRequiredArgument(): void
    {
        $this->tester->execute(['Smerteliko']);

        $this->assertSame(0, $this->tester->getStatusCode());

        $output = $this->tester->getDisplay();

        $this->assertStringContainsString('Hello, Smerteliko!', $output);
    }

    public function testExecuteWithYellOption(): void
    {
        $this->tester->execute(['Smerteliko', '--yell']);

        $output = $this->tester->getDisplay();
        $this->assertStringContainsString('HELLO, SMERTELIKO!', $output);
    }

    public function testExecuteWithRepeatOption(): void
    {
        $this->tester->execute(['Smerteliko', '--repeat=3']);

        $output = $this->tester->getDisplay();

        $count = substr_count($output, 'Hello, Smerteliko!');
        $this->assertSame(3, $count);
    }

    public function testExecuteThrowsExceptionOnMissingRequiredArgument(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "name")');

        $this->tester->execute([]);
    }
}