<?php

namespace App\Tests\SonataIntegrationBundle\User\Action;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnlockUserActionTest extends WebTestCase
{
    protected function login(KernelBrowser $client, string $email): void
    {
        $client->loginUser($this->getUser($email), 'user');
    }

    protected function getUser(string $email): User
    {
        return static::getContainer()
            ->get(EntityManagerInterface::class)
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    public function testUnlock(): void
    {
        $client = static::createClient();

        $this->login($client, 'admin@example.com');

        $user = $this->getUser('locked@example.com');

        $userLock = $user->getLocks()['manual-lock'];

        $userLock->setUnlockUntil(null);

        $client->request('GET', sprintf('/admin/app/user/%s/unlock', $user->getId()));

        static::assertResponseStatusCodeSame(302);
        static::assertResponseHeaderSame(
            'Location',
            sprintf('/admin/app/user/%s/show', $user->getId())
        );

        $client->followRedirect();

        static::assertResponseIsSuccessful();

        static::assertSelectorTextContains('.alert-success', 'The user lock has been unlock for the next 24 hour');

        $userLock = static::getContainer()
            ->get(EntityManagerInterface::class)
            ->find(UserLock::class, $userLock->getId());

        static::assertEqualsWithDelta(new DateTimeImmutable('+ 24 hours'), $userLock->getUnlockUntil(), 2);
    }

    public function testNoAccess(): void
    {
        $client = static::createClient();

        $this->login($client, 'locked@example.com');

        $user = $this->getUser('admin@example.com');

        $client->request('GET', sprintf('/admin/app/user/%s/unlock', $user->getId()));

        static::assertResponseStatusCodeSame(403);
    }
}
