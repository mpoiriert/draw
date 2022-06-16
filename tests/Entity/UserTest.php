<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Tests\TestCase;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Bundle\UserBundle\Message\NewUserLockMessage;
use Draw\Bundle\UserBundle\Message\UserLockDelayedActivationMessage;
use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\ReceivedStamp;

class UserTest extends TestCase
{
    private $entityManager;

    private $transportTester;

    public function setUp(): void
    {
        $this->entityManager = static::getService(EntityManagerInterface::class);
        $this->transportTester = static::getTransportTester('sync');
        $this->transportTester->reset();
    }

    /**
     * @before
     * @after
     */
    public function cleanUp(): void
    {
        static::getService(EntityManagerInterface::class)
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

        $user->lock(new UserLock(UserLock::REASON_MANUAL_LOCK));

        $entityManager = $this->getService(EntityManagerInterface::class);

        $entityManager->persist($user);
        $entityManager->flush();

        $this->transportTester->assertMessageMatch(NewUserLockMessage::class);
    }

    public function testLockDelayed(): void
    {
        $user = new User();
        $user->setEmail('test-lock@example.com');

        $user->lock(
            $userLock = (new UserLock(UserLock::REASON_MANUAL_LOCK))
                ->setLockOn(new DateTimeImmutable('+ 5 minutes'))
        );

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $envelope = $this->transportTester->getTransport()->get()[0];

        static::assertInstanceOf(NewUserLockMessage::class, $envelope->getMessage());

        $this->transportTester->reset();
        static::getService(MessageBusInterface::class)
            ->dispatch($envelope->with(new ReceivedStamp('sync')));

        $this->transportTester->assertMessageMatch(UserLockDelayedActivationMessage::class);

        $stamp = $this->transportTester->getTransport()->get()[0]->last(SearchableTagStamp::class);

        static::assertTrue($stamp->getEnforceUniqueness());
        static::assertSame(
            [
                'activateUserLock:'.$userLock->getReason(),
                'userId:'.$userLock->getUser()->getId(),
            ],
            $stamp->getTags(),
        );
    }
}
