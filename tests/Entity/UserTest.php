<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\Messenger\TransportTester;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireTransportTester;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Bundle\UserBundle\Message\NewUserLockMessage;
use Draw\Bundle\UserBundle\Message\UserLockDelayedActivationMessage;
use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use PHPUnit\Framework\Attributes\After;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class UserTest extends TestCase implements AutowireInterface
{
    #[AutowireService]
    private EntityManagerInterface $entityManager;

    #[AutowireTransportTester('sync')]
    private TransportTester $transportTester;

    #[AutowireService]
    private MessageBusInterface $messageBus;

    #[After]
    public function cleanUp(): void
    {
        $this->entityManager
            ->createQueryBuilder()
            ->delete(User::class, 'user')
            ->where('user.email = :email')
            ->setParameter('email', 'test-lock@example.com')
            ->getQuery()
            ->execute();
    }

    public function testLock(): void
    {
        $user = new User();
        $user->setEmail('test-lock@example.com');

        $user->setManualLock(true);

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $this->transportTester->assertMessageMatch(NewUserLockMessage::class);
    }

    public function testLockDelayed(): void
    {
        $user = new User();
        $user->setEmail('test-lock@example.com');

        $user->lock(
            $userLock = (new UserLock(uniqid('reason-')))
                ->setLockOn(new \DateTimeImmutable('+ 5 minutes'))
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $envelope = $this->transportTester->getTransport()->get()[0];

        static::assertInstanceOf(NewUserLockMessage::class, $envelope->getMessage());

        $this->transportTester->reset();
        $this->messageBus->dispatch($envelope->with(new ReceivedStamp('sync')));

        $this->transportTester->assertMessageMatch(UserLockDelayedActivationMessage::class);

        $stamp = $this->transportTester->getTransport()->getSent()[0]->last(SearchableTagStamp::class);

        static::assertTrue($stamp->getEnforceUniqueness());
        static::assertSame(
            [
                'activateUserLock:'.$userLock->getReason(),
                'userId:'.$userLock->getUser()->getUserIdentifier(),
            ],
            $stamp->getTags(),
        );
    }
}
