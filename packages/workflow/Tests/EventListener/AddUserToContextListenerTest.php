<?php

namespace Draw\Component\Workflow\Tests\EventListener;

use Draw\Component\Security\Core\Security;
use Draw\Component\Workflow\EventListener\AddUserToContextListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Marking;

/**
 * @internal
 */
class AddUserToContextListenerTest extends TestCase
{
    public function testGetSubscribedEvents(): void
    {
        $object = new AddUserToContextListener(
            static::createStub(Security::class)
        );

        static::assertSame(
            ['workflow.transition' => 'addUserToContext'],
            $object::getSubscribedEvents()
        );
    }

    public function testAddUserToContextNoUser(): void
    {
        $object = new AddUserToContextListener(
            static::createStub(Security::class)
        );

        $transitionEvent = new TransitionEvent(
            new \stdClass(),
            static::createStub(Marking::class),
        );

        $transitionEvent->setContext($originalContext = [
            uniqid('key-') => uniqid('value-'),
        ]);

        $object->addUserToContext($transitionEvent);

        static::assertSame(
            $originalContext,
            $transitionEvent->getContext()
        );
    }

    public function testAddUserToContextProperUser(): void
    {
        $object = new AddUserToContextListener(
            $security = $this->createMock(Security::class)
        );

        $transitionEvent = new TransitionEvent(
            new \stdClass(),
            static::createStub(Marking::class),
        );

        $transitionEvent->setContext($originalContext = [
            uniqid('key-') => uniqid('value-'),
        ]);

        $security
            ->expects(static::once())
            ->method('getUser')
            ->willReturn($user = static::createStub(UserInterface::class))
            ->seal()
        ;

        $object->addUserToContext($transitionEvent);

        static::assertSame(
            array_merge(
                $originalContext,
                ['_user' => $user]
            ),
            $transitionEvent->getContext()
        );
    }
}
