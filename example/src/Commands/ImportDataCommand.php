<?php

namespace App\Commands;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;
use Smerteliko\MicroCli\Style\Style;

#[AsConsoleCommand(name: 'import:data', description: 'Simulates a long-running data import process')]
class ImportDataCommand extends Command
{
	public function execute(InputInterface $input, OutputInterface $output): int
	{
		$io = new Style($input, $output);

		$io->title('Starting Data Import');
		$io->text('Fetching records from external API...');

		$totalRecords = 50;

		// 1. Инициализируем ProgressBar
		$progressBar = $io->createProgressBar($totalRecords);

		// 2. Запускаем
		$progressBar->start();

		for ($i = 0; $i < $totalRecords; $i++) {
			// Эмулируем задержку (тяжелую работу)
			usleep(50000); // 50 миллисекунд

			// 3. Продвигаем прогресс
			$progressBar->advance();
		}

		// 4. Завершаем
		$progressBar->finish();

		$io->success('Import completed successfully!');


		return 0;
	}
}