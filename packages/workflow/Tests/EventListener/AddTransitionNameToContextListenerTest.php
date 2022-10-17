<?php

namespace Draw\Component\Workflow\Tests\EventListener;

use Draw\Component\Workflow\EventListener\AddTransitionNameToContextListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Marking;
use Symfony\Component\Workflow\Transition;

class AddTransitionNameToContextListenerTest extends TestCase
{
    private AddTransitionNameToContextListener $object;

    protected function setUp(): void
    {
        $this->object = new AddTransitionNameToContextListener();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            ['workflow.transition' => 'addTransitionToContext'],
            $this->object::getSubscribedEvents()
        );
    }

    public function testAddTransitionToContext(): void
    {
        $this->object->addTransitionToContext(
            $event = new TransitionEvent(
                (object) [],
                new Marking(),
                new Transition($transitionName = uniqid('transition'), uniqid('from'), uniqid('to'))
            )
        );

        static::assertSame(
            $transitionName,
            $event->getContext()['_transitionName']
        );
    }
}
