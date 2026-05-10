<?php

namespace Smerteliko\MicroCli\Helper;

use Smerteliko\MicroCli\Output\OutputInterface;

class ProgressBar
{
	private OutputInterface $output;
	private int $max;
	private int $step = 0;
	private int $barWidth = 28;

	private string $barChar = '=';
	private string $emptyBarChar = '-';
	private string $progressChar = '>';

	public function __construct(OutputInterface $output, int $max = 0)
	{
		$this->output = $output;
		$this->max = $max;
	}

	public function start(?int $max = null): void
	{
		if ($max !== null) {
			$this->max = $max;
		}
		$this->step = 0;
		$this->display();
	}

	public function advance(int $step = 1): void
	{
		$this->step += $step;
		if ($this->max > 0 && $this->step > $this->max) {
			$this->step = $this->max;
		}
		$this->display();
	}

	public function finish(): void
	{
		if ($this->max > 0) {
			$this->step = $this->max;
		}
		$this->display();
		$this->output->writeln('');
	}

	private function display(): void
	{
		$percent = $this->max > 0 ? (float) $this->step / $this->max : 0;
		$percentFormatted = floor($percent * 100);

		$completeBars = (int) floor($percent * $this->barWidth);
		$emptyBars = $this->barWidth - $completeBars;

		$bar = str_repeat($this->barChar, $completeBars);
		if ($emptyBars > 0) {
			$bar .= $this->progressChar;
			$bar .= str_repeat($this->emptyBarChar, max(0, $emptyBars - 1));
		}

		$message = sprintf("\r  [%s] %3d%% (%d/%d)", $bar, $percentFormatted, $this->step, $this->max);

		$this->output->write("<info>{$message}</info>");
	}
}