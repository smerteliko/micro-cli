<?php

namespace Smerteliko\MicroCli\Macro;

class MacroManager
{
	private string $file;
	private array $state;

	/**
	 * @throws \JsonException
	 */
	public function __construct()
	{
		$this->file = getcwd() . '/.macros.json';
		$this->load();
	}

	/**
	 * @throws \JsonException
	 */
	private function load(): void
	{
		if (file_exists($this->file)) {
			$this->state = json_decode(file_get_contents($this->file),
			                           TRUE,
			                           512,
			                           JSON_THROW_ON_ERROR)
				?: [ 'recording' => null, 'macros' => []];
		} else {
			$this->state = ['recording' => null, 'macros' => []];
		}
	}

	/**
	 * @throws \JsonException
	 */
	private function save(): void
	{
		file_put_contents($this->file,
		                  json_encode($this->state,
		                              JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT));
	}

	/**
	 * @throws \JsonException
	 */
	public function startRecording(string $name): void
	{
		$this->state['recording'] = $name;
		$this->state['macros'][$name] = [];
		$this->save();
	}

	/**
	 * @throws \JsonException
	 */
	public function stopRecording(): ?string
	{
		$name = $this->state['recording'];
		$this->state['recording'] = null;
		$this->save();
		return $name;
	}

	public function isRecording(): bool
	{
		return $this->state['recording'] !== null;
	}

	public function getRecordingName(): ?string
	{
		return $this->state['recording'];
	}

	/**
	 * @throws \JsonException
	 */
	public function addCommand(string $command): void
	{
		$name = $this->state['recording'];
		if ($name !== null) {
			$this->state['macros'][$name][] = $command;
			$this->save();
		}
	}

	public function getMacro(string $name): ?array
	{
		return $this->state['macros'][$name] ?? null;
	}
}