<?php

namespace Smerteliko\MicroCli\Scheduling;

use DateTimeInterface;
use DateTimeImmutable;
use InvalidArgumentException;

class CronExpression
{
	private string $expression;

	public function __construct(string $expression)
	{
		$this->expression = trim($expression);
	}

	/**
	 * Проверяет, настало ли время выполнения для данного выражения
	 */
	public function isDue(?DateTimeInterface $date = null): bool
	{
		$date = $date ?? new DateTimeImmutable();

		$parts = preg_split('/\s+/', $this->expression);

		if (count($parts) !== 5) {
			throw new InvalidArgumentException("Invalid cron expression: '{$this->expression}'");
		}

		return $this->matchPart($parts[0], (int) $date->format('i'))
			&& $this->matchPart($parts[1], (int) $date->format('H'))
			&& $this->matchPart($parts[2], (int) $date->format('d'))
			&& $this->matchPart($parts[3], (int) $date->format('m'))
			&& $this->matchPart($parts[4], (int) $date->format('w'));
	}

	private function matchPart(string $part, int $currentValue): bool
	{
		if ($part === '*') {
			return true;
		}

		if (str_starts_with($part, '*/')) {
			$step = (int) substr($part, 2);
			return $step > 0 && $currentValue % $step === 0;
		}

		if (str_contains($part, ',')) {
			$values = array_map('intval', explode(',', $part));
			return in_array($currentValue, $values, true);
		}

		if (str_contains($part, '-')) {
			[$min, $max] = array_map('intval', explode('-', $part, 2));
			return $currentValue >= $min && $currentValue <= $max;
		}

		return (int) $part === $currentValue;
	}
}