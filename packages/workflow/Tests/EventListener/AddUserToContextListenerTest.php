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
            $this->createStub(Security::class)
        );

        $this->assertSame(
            ['workflow.transition' => 'addUserToContext'],
            $object::getSubscribedEvents()
        );
    }

    public function testAddUserToContextNoUser(): void
    {
        $object = new AddUserToContextListener(
            $this->createStub(Security::class)
        );

        $transitionEvent = new TransitionEvent(
            new \stdClass(),
            $this->createStub(Marking::class),
        );

        $transitionEvent->setContext($originalContext = [
            uniqid('key-') => uniqid('value-'),
        ]);

        $object->addUserToContext($transitionEvent);

        $this->assertSame(
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
            $this->createStub(Marking::class),
        );

        $transitionEvent->setContext($originalContext = [
            uniqid('key-') => uniqid('value-'),
        ]);

        $security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createStub(UserInterface::class))
        ;

        $object->addUserToContext($transitionEvent);

        $this->assertSame(
            array_merge(
                $originalContext,
                ['_user' => $user]
            ),
            $transitionEvent->getContext()
        );
    }
}
