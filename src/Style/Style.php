<?php

namespace Smerteliko\MicroCli\Style;

use Smerteliko\MicroCli\Helper\FormatterHelper;
use Smerteliko\MicroCli\Helper\ProcessHelper;
use Smerteliko\MicroCli\Helper\ProgressBar;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;

class Style
{
	public function __construct(
		private InputInterface $input,
		private OutputInterface $output
	) {
	}

	public function title(string $message): void
	{
		$this->newLine();
		$this->output->writeln("<comment>{$message}</comment>");
		$this->output->writeln("<comment>" . str_repeat('=', mb_strlen($message)) . "</comment>");
		$this->newLine();
	}

	public function success(string $message): void
	{
		$this->block($message, 'OK', 'info');
	}

	public function error(string $message): void
	{
		$this->block($message, 'ERROR', 'error');
	}

	public function warning(string $message): void
	{
		$this->block($message, 'WARNING', 'comment');
	}

	public function info(string $message): void
	{
		$this->block($message, 'INFO', 'info');
	}

	public function text(string|array $message): void
	{
		$messages = is_array($message) ? $message : [$message];
		foreach ($messages as $msg) {
			$this->output->writeln(" {$msg}");
		}
	}

	public function newLine(int $count = 1): void
	{
		for ($i = 0; $i < $count; $i++) {
			$this->output->writeln('');
		}
	}

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

	public function table(array $headers, array $rows): void
	{
		$table = new Table($this->output);
		$table->setHeaders($headers)
		      ->setRows($rows)
		      ->render();

		$this->newLine();
	}

	public function createProgressBar(int $max = 0): ProgressBar
	{
		return new ProgressBar($this->output, $max);
	}

	public function runProcess(string|array $command): int
	{
		$process = new ProcessHelper($this->output);
		$this->newLine();
		$exitCode = $process->run($command);
		$this->newLine();

		return $exitCode;
	}

	public function truncate(string $message, int $length): string
	{
		return ( new FormatterHelper() )->truncate($message, $length);
	}

	public function askHidden(string $question): string
	{
		$this->output->write("<comment>{$question}: </comment>");

		$sttyMode = shell_exec('stty -g');
		shell_exec('stty -echo');

		$handle = fopen('php://stdin', 'r');
		$answer = trim(fgets($handle));
		fclose($handle);

		shell_exec(sprintf('stty %s', $sttyMode));

		$this->newLine();

		return $answer;
	}

	public function choice(string $question, array $choices, mixed $default = null, bool $multiSelect = false): string|array
	{
		if (empty($choices)) {
			throw new \InvalidArgumentException('Choices array cannot be empty.');
		}

		$this->output->writeln("<comment>{$question}</comment>");

		if ($multiSelect) {
			$this->output->writeln("  <info>(Separate multiple answers with a comma, e.g., 1,3)</info>");
		}

		$isAssoc = array_keys($choices) !== range(0, count($choices) - 1);
		$displayMap = [];

		foreach ($choices as $key => $value) {
			$displayKey = $isAssoc ? (string) $key : (string) ($key + 1);
			$displayMap[$displayKey] = $value;
			$this->output->writeln(sprintf("  [<info>%s</info>] %s", $displayKey, $value));
		}

		$defaultPrompt = $default !== null ? " [<info>{$default}</info>]" : '';

		while (true) {
			$this->output->write("<comment> >{$defaultPrompt} </comment>");
			$handle = fopen('php://stdin', 'r');
			$answer = trim(fgets($handle));
			fclose($handle);

			if ($answer === '' && $default !== null) {
				if ($multiSelect) {
					// Если дефолт передан как строка "1,3", разбиваем её
					return is_string($default) ? array_map('trim', explode(',', $default)) : (array) $default;
				}
				return (string) $default;
			}

			$rawAnswers = $multiSelect ? array_map('trim', explode(',', $answer)) : [$answer];
			$selectedValues = [];
			$hasError = false;

			foreach ($rawAnswers as $ans) {
				if (array_key_exists($ans, $displayMap)) {
					$selectedValues[] = $displayMap[$ans];
				} elseif (in_array($ans, $displayMap, true)) {
					$selectedValues[] = $ans;
				} else {
					$this->error(sprintf('Value "%s" is invalid.', $ans));
					$hasError = true;
					break; // Прерываем проверку, просим ввести заново
				}
			}

			if (!$hasError) {
				return $multiSelect ? $selectedValues : $selectedValues[0];
			}
		}
	}
}