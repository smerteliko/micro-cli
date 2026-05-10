<?php

namespace Smerteliko\MicroCli\Command\System;

use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;
use Smerteliko\MicroCli\Config\Config;
use Smerteliko\MicroCli\Scheduling\CronExpression;

class ScheduleRunCommand extends Command
{
	private Config $config;

	public function __construct(Config $config)
	{
		$this->config = $config;
		parent::__construct();
	}

	protected function configure(): void
	{
		$this->setName('schedule:run')
		     ->setDescription('Runs scheduled commands');
	}

	public function execute(InputInterface $input, OutputInterface $output): int
	{
		// 1. Собираем расписание из конфигов
		$schedule = $this->config->get('schedule', []);

		// 2. Собираем расписание из самих команд (оно имеет приоритет или дополняет)
		foreach ($this->getApplication()->all() as $command) {
			if ($cron = $command->getSchedule()) {
				// Если у команды есть расписание, добавляем её
				$schedule[$command->getName()] = $cron;
			}
		}

		if (empty($schedule)) {
			$output->writeln("<info>No scheduled commands found.</info>");
			return 0;
		}

		$output->writeln("<comment>Checking scheduled tasks...</comment>");
		$ranAny = false;

		foreach ($schedule as $commandString => $cronString) {
			$cron = new CronExpression($cronString);

			if ($cron->isDue()) {
				$output->writeln("<info>[Running]</info> {$commandString}");

				$executable = $_SERVER['PHP_SELF'];
				// Запускаем в фоне, чтобы одна долгая задача не стопанула весь планировщик
				$processCommand = sprintf('php %s %s > /dev/null 2>&1 &', escapeshellarg($executable), $commandString);

				exec($processCommand);
				$ranAny = true;
			} else {
				// Используем наш новый метод isVerbose()
				if ($output->isVerbose()) {
					$output->writeln("<comment>[Skipped]</comment> {$commandString} (Cron: {$cronString})");
				}
			}
		}

		if (!$ranAny) {
			$output->writeln("Nothing to run at this minute.");
		}

		return 0;
	}
}