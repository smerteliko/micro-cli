<?php

namespace Smerteliko\MicroCli\Style;

use Smerteliko\MicroCli\Output\OutputInterface;

class Table
{
	private OutputInterface $output;
	private array $headers = [];
	private array $rows = [];
	private array $columnWidths = [];

	public function __construct(OutputInterface $output)
	{
		$this->output = $output;
	}

	public function setHeaders(array $headers): self
	{
		$this->headers = array_values($headers);
		return $this;
	}

	public function addRow(array $row): self
	{
		$this->rows[] = array_values($row);
		return $this;
	}

	public function setRows(array $rows): self
	{
		$this->rows = [];
		foreach ($rows as $row) {
			$this->addRow($row);
		}
		return $this;
	}

	public function render(): void
	{
		$this->calculateColumnWidths();

		$separator = $this->getSeparator();

		$this->output->writeln($separator);

		if (!empty($this->headers)) {
			$this->renderRow($this->headers, 'info');
			$this->output->writeln($separator);
		}

		foreach ($this->rows as $row) {
			$this->renderRow($row);
		}

		if (!empty($this->rows)) {
			$this->output->writeln($separator);
		}
	}

	private function calculateColumnWidths(): void
	{
		$this->columnWidths = [];
		$allRows = array_merge([$this->headers], $this->rows);

		foreach ($allRows as $row) {
			foreach ($row as $i => $cell) {
				// Strip tags for accurate length calculation
				$cleanCell = strip_tags((string) $cell);
				$length = mb_strlen($cleanCell);

				if (!isset($this->columnWidths[$i]) || $length > $this->columnWidths[$i]) {
					$this->columnWidths[$i] = $length;
				}
			}
		}
	}

	private function getSeparator(): string
	{
		$parts = [];
		foreach ($this->columnWidths as $width) {
			$parts[] = str_repeat('-', $width + 2);
		}
		return '+' . implode('+', $parts) . '+';
	}

	private function renderRow(array $row, ?string $colorTag = null): void
	{
		$cells = [];
		foreach ($this->columnWidths as $i => $width) {
			$cellValue = isset($row[$i]) ? (string) $row[$i] : '';

			// Handle color tags if provided
			if ($colorTag) {
				$cellValue = "<{$colorTag}>{$cellValue}</{$colorTag}>";
			}

			// Calculate padding based on raw string length without tags
			$cleanLength = mb_strlen(strip_tags((string) ($row[$i] ?? '')));
			$padding = $width - $cleanLength;

			$cells[] = ' ' . $cellValue . str_repeat(' ', $padding) . ' ';
		}

		$this->output->writeln('|' . implode('|', $cells) . '|');
	}
}