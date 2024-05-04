<?php

namespace App\Tests\SonataIntegrationBundle\User\Action;

use App\Tests\SonataIntegrationBundle\WebTestCaseTrait;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class UnlockUserActionTest extends WebTestCase implements AutowiredInterface
{
    use WebTestCaseTrait;

    public function testUnlock(): void
    {
        $this->login('admin@example.com');

        $user = $this->getUser('locked@example.com');

        $userLock = $user->getLocks()['manual-lock'];

        $userLock->setUnlockUntil(null);

        static::$client->request('GET', sprintf('/admin/app/user/%s/unlock', $user->getId()));

        static::assertResponseStatusCodeSame(302);
        static::assertResponseHeaderSame(
            'Location',
            sprintf('/admin/app/user/%s/show', $user->getId())
        );

        static::$client->followRedirect();

        static::assertResponseIsSuccessful();

        static::assertSelectorTextContains('.alert-success', 'The user lock has been unlock for the next 24 hour');

        $userLock = $this->entityManager->find(
            UserLock::class,
            $userLock->getId()
        );

        static::assertEqualsWithDelta(new \DateTimeImmutable('+ 24 hours'), $userLock->getUnlockUntil(), 2);
    }

    public function testNoAccess(): void
    {
        $this->login('locked@example.com');

        $user = $this->getUser('admin@example.com');

        static::$client->request('GET', sprintf('/admin/app/user/%s/unlock', $user->getId()));

        static::assertResponseStatusCodeSame(403);
    }
}
