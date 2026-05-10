<?php

namespace App\Commands;

use Smerteliko\MicroCli\Attributes\AsConsoleCommand;
use Smerteliko\MicroCli\Attributes\Argument;
use Smerteliko\MicroCli\Attributes\Option;
use Smerteliko\MicroCli\Command\Command;
use Smerteliko\MicroCli\Input\InputInterface;
use Smerteliko\MicroCli\Output\OutputInterface;

#[AsConsoleCommand(name: 'greet', description: 'Advanced greeting command for testing all input cases')]
class GreetCommand extends Command
{
	// Обязательный аргумент. Если не передать, InputInterface::validate() выбросит исключение
	#[Argument(description: 'Who do you want to greet?', required: true)]
	public string $name;

	// Необязательный аргумент с дефолтным значением
	#[Argument(description: 'Custom greeting word', required: false, default: 'Hello')]
	public string $word;

	// Флаг (булево значение). Если указан --yell или -y, будет true
	#[Option(shortcut: 'y', description: 'Yell the output (uppercase)', default: false)]
	public bool $yell = false;

	// Строковая опция с дефолтным значением
	#[Option(shortcut: 'c', description: 'Color tag (info, error, comment)', default: 'info')]
	public string $color;

	// Опция с числовым значением для проверки приведения типов
	#[Option(shortcut: 'r', description: 'Number of times to repeat the greeting', default: 1)]
	public int $repeat;

	public function execute(InputInterface $input, OutputInterface $output): int
	{
		// 1. Формируем базовый текст
		$text = "{$this->word}, {$this->name}!";

		// 2. Применяем флаг --yell
		if ($this->yell) {
			$text = strtoupper($text);
		}

		// 3. Защита от неизвестных тегов цвета (чтобы не сломать консоль)
		$tag = in_array($this->color, ['info', 'error', 'comment']) ? $this->color : 'info';

		// 4. Выводим нужное количество раз (проверка $repeat)
		for ($i = 0; $i < $this->repeat; $i++) {
			$output->writeln("<{$tag}>{$text}</{$tag}>");
		}
		return 0; // Код успешного завершения
	}
}