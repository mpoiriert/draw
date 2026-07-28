<?php

namespace Draw\Bundle\TesterBundle\EventDispatcher;

use PHPUnit\Framework\TestCase;

trigger_deprecation('draw/tester-bundle', '0.10.44', 'The "%s" class is deprecated, use "%s" instead.', EventListenerTestTrait::class, EventDispatcherTesterTrait::class);

/**
 * @deprecated
 */
trait EventListenerTestTrait
{
    public static function assertEventListenersRegistered(string $className, array $expectedEvents): void
    {
        $listeners = static::getContainer()->get('event_dispatcher')->getListeners();

        $events = [];
        foreach ($listeners as $eventName => $eventListeners) {
            foreach ($eventListeners as $eventListener) {
                if (!\is_array($eventListener)) {
                    continue;
                }
                if (!\is_object($eventListener[0])) {
                    continue;
                }
                if ($eventListener[0] instanceof $className) {
                    $events[$eventName][] = $eventListener[1];
                }
            }
        }

        ksort($events);
        ksort($expectedEvents);

        TestCase::assertSame($expectedEvents, $events);
    }
}
