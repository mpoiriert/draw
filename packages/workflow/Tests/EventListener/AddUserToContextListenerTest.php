<?php

namespace Draw\Component\Workflow\Tests\EventListener;

use Draw\Component\Security\Core\Security;
use Draw\Component\Workflow\EventListener\AddUserToContextListener;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Workflow\Event\TransitionEvent;
use Symfony\Component\Workflow\Marking;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
class AddUserToContextListenerTest extends TestCase
{
    private AddUserToContextListener $object;

    private Security&MockObject $security;

    protected function setUp(): void
    {
        $this->object = new AddUserToContextListener(
            $this->security = $this->createMock(Security::class)
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            ['workflow.transition' => 'addUserToContext'],
            $this->object::getSubscribedEvents()
        );
    }

    public function testAddUserToContextNoUser(): void
    {
        $transitionEvent = new TransitionEvent(
            new \stdClass(),
            $this->createStub(Marking::class),
        );

        $transitionEvent->setContext($originalContext = [
            uniqid('key-') => uniqid('value-'),
        ]);

        $this->object->addUserToContext($transitionEvent);

        $this->assertSame(
            $originalContext,
            $transitionEvent->getContext()
        );
    }

    public function testAddUserToContextProperUser(): void
    {
        $transitionEvent = new TransitionEvent(
            new \stdClass(),
            $this->createStub(Marking::class),
        );

        $transitionEvent->setContext($originalContext = [
            uniqid('key-') => uniqid('value-'),
        ]);

        $this->security
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($user = $this->createMock(UserInterface::class))
        ;

        $this->object->addUserToContext($transitionEvent);

        $this->assertSame(
            array_merge(
                $originalContext,
                ['_user' => $user]
            ),
            $transitionEvent->getContext()
        );
    }
}
