<?php
namespace Smerteliko\MicroCli\Output;

interface OutputInterface {
	public function write(string $message): void;

	public function writeln(string $message): void;
}