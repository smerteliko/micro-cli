<?php

namespace Smerteliko\MicroCli\EventDispatcher;

class EventDispatcher
{
	/** @var array<string, callable[]> */
	private array $listeners = [];

	public function addListener(string $eventName, callable $listener): void
	{
		$this->listeners[$eventName][] = $listener;
	}

	/**
	 * Вызывает всех слушателей, подписанных на данное событие
	 */
	public function dispatch(object $event, ?string $eventName = null): object
	{
		$eventName = $eventName ?? get_class($event);

		foreach ($this->listeners[$eventName] ?? [] as $listener) {
			$listener($event);

			if (method_exists($event, 'isPropagationStopped') && $event->isPropagationStopped()) {
				break;
			}
		}

		return $event;
	}
}