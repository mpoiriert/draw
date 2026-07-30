<?php

namespace App\Tests\OpenApi\EventListener;

use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\EventListener\ResponseApiExceptionListener;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
class ResponseApiExceptionListenerTest extends KernelTestCase implements AutowiredInterface
{
    #[AutowireService]
    private ResponseApiExceptionListener $object;

    #[AutowireService]
    private EventDispatcherInterface $eventDispatcher;

    public function test(): void
    {
        $expectedEvents = [
            'kernel.exception' => ['onKernelException'],
            PreDumpRootSchemaEvent::class => ['addErrorDefinition'],
        ];

        $className = $this->object::class;

        $events = [];

        foreach ($this->eventDispatcher->getListeners() as $eventName => $listeners) {
            foreach ($listeners as $listener) {
                if (!\is_array($listener) || \count($listener) < 2) {
                    continue;
                }

                if (!\is_object($listener[0])) {
                    continue;
                }

                if ($listener[0] instanceof $className) {
                    $events[$eventName][] = $listener[1];
                }
            }
        }

        ksort($expectedEvents);
        ksort($events);

        $this->assertSame($expectedEvents, $events);
    }
}
