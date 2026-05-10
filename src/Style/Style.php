<?php

namespace Smerteliko\MicroCli\Style;

use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;

class Style
{
	public function __construct(
		private InputInterface $input,
		private OutputInterface $output
	) {
	}

	/**
	 * Prints a nicely formatted title.
	 */
	public function title(string $message): void
	{
		$this->newLine();
		$this->output->writeln("<comment>{$message}</comment>");
		$this->output->writeln("<comment>" . str_repeat('=', mb_strlen($message)) . "</comment>");
		$this->newLine();
	}

	/**
	 * Prints a success block.
	 */
	public function success(string $message): void
	{
		$this->block($message, 'OK', 'info');
	}

	/**
	 * Prints an error block.
	 */
	public function error(string $message): void
	{
		$this->block($message, 'ERROR', 'error');
	}

	/**
	 * Prints a warning block.
	 */
	public function warning(string $message): void
	{
		$this->block($message, 'WARNING', 'comment');
	}

	/**
	 * Prints an informational block.
	 */
	public function info(string $message): void
	{
		$this->block($message, 'INFO', 'info');
	}

	/**
	 * Prints simple text, optionally handling an array of lines.
	 */
	public function text(string|array $message): void
	{
		$messages = is_array($message) ? $message : [$message];
		foreach ($messages as $msg) {
			$this->output->writeln(" {$msg}");
		}
	}

	/**
	 * Adds blank lines.
	 */
	public function newLine(int $count = 1): void
	{
		for ($i = 0; $i < $count; $i++) {
			$this->output->writeln('');
		}
	}

	/**
	 * Internal method to format blocks.
	 */
	private function block(string $message, string $type, string $tag): void
	{
		$this->newLine();
		$this->output->writeln("<{$tag}> [{$type}] {$message} </{$tag}>");
		$this->newLine();
	}

	public function ask(string $question, ?string $default = null): string
	{
		$prompt = $default ? "{$question} [<info>{$default}</info>]: " : "{$question}: ";
		$this->output->write("<comment>{$prompt}</comment>");

		$handle = fopen('php://stdin', 'r');
		$answer = trim(fgets($handle));
		fclose($handle);

		return $answer === '' ? (string) $default : $answer;
	}

	/**
	 * Asks the user a yes/no confirmation question.
	 */
	public function confirm(string $question, bool $default = true): bool
	{
		$choices = $default ? '[Y/n]' : '[y/N]';
		$prompt = "{$question} <info>{$choices}</info>: ";
		$this->output->write("<comment>{$prompt}</comment>");

		$handle = fopen('php://stdin', 'r');
		$answer = strtolower(trim(fgets($handle)));
		fclose($handle);

		if ($answer === '') {
			return $default;
		}

		return in_array($answer, ['y', 'yes', '1', 'true'], true);
	}
}