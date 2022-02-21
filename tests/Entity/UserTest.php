<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\UserBundle\AccountLocker\Entity\UserLock;
use Draw\Bundle\UserBundle\AccountLocker\Message\NewUserLockMessage;

class UserTest extends TestCase
{
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

        $transportTester = static::getTransportTester('sync');

        $transportTester->assertMessageMatch(NewUserLockMessage::class);
    }
}
