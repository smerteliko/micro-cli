<?php

namespace Tests\Event;

use PHPUnit\Framework\TestCase;
use Smerteliko\MicroCli\EventDispatcher\EventDispatcher;

class EventDispatcherTest extends TestCase
{
    public function testDispatchesEventToListeners(): void
    {
        $dispatcher = new EventDispatcher();
        $called = false;

        $event = new \stdClass();
        $dispatcher->addListener(\stdClass::class, function ($e) use (&$called) {
            $called = true;
        });

        $dispatcher->dispatch($event);
        $this->assertTrue($called);
    }

    public function testStopsPropagation(): void
    {
        $dispatcher = new EventDispatcher();
        $count = 0;

        $event = new class {
            public bool $stopped = false;
            public function isPropagationStopped(): bool { return $this->stopped; }
        };

        $dispatcher->addListener(get_class($event), function ($e) use (&$count) {
            $count++;
            $e->stopped = true;
        });

        $dispatcher->addListener(get_class($event), function ($e) use (&$count) {
            $count++;
        });

        $dispatcher->dispatch($event);
        $this->assertEquals(1, $count);
    }
}