<?php

namespace Tests\Dotenv;

use PHPUnit\Framework\TestCase;
use Smerteliko\MicroCli\Dotenv\Dotenv;

class DotenvTest extends TestCase
{
    public function testLoadsEnvironmentVariables(): void
    {
        $path = sys_get_temp_dir() . '/.env.test';
        file_put_contents($path, "TEST_VAR=hello\n#Comment\nQUOTED=\"world\"");

        $dotenv = new Dotenv();
        $dotenv->load($path);

        $this->assertEquals('hello', $_ENV['TEST_VAR']);
        $this->assertEquals('world', $_ENV['QUOTED']);

        unlink($path);
    }
}