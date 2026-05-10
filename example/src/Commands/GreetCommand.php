<?php
namespace App\Commands;

use Smerteliko\MicroCli\Application;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;
class GreetCommand extends Command
{
	protected function configure(): void
	{
		$this->setName('greet')
		     ->setDescription('Prints a greeting to the terminal')
		     ->addArgument('name', 'The name of the user', 'World')
		     ->addOption('yell', 'If set, the greeting will be uppercase', false);
	}

	public function execute(InputInterface $input, OutputInterface $output): int
	{
		// Пока мы получаем аргумент по индексу (скоро это исправим), но опции уже по имени
		$name = $input->getArgument(0) ?? $this->getRegisteredArguments()['name']['default'];
		$yell = $input->hasOption('yell');

		$message = "Hello, {$name}!";
		if ($yell) {
			$message = strtoupper($message);
		}

		$output->writeln("<info>{$message}</info>");

		return 0;
	}
}