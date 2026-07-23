<?php

namespace Draw\Component\Application\Tests\Versioning\Event;

use Draw\Component\Application\Versioning\Event\FetchRunningVersionEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(FetchRunningVersionEvent::class)]
class FetchRunningVersionEventTest extends TestCase
{
    private FetchRunningVersionEvent $event;

    protected function setUp(): void
    {
        $this->event = new FetchRunningVersionEvent();
    }

    public function testRunningVersionMutator(): void
    {
        $this->assertFalse($this->event->isPropagationStopped());

        $this->assertNull($this->event->getRunningVersion());

        $this->assertSame(
            $this->event,
            $this->event->setRunningVersion($value = uniqid())
        );

        $this->assertSame(
            $value,
            $this->event->getRunningVersion()
        );

        $this->assertTrue($this->event->isPropagationStopped());
    }
}
