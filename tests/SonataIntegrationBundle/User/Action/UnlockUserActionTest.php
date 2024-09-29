<?php

namespace App\Tests\SonataIntegrationBundle\User\Action;

use App\Tests\SonataIntegrationBundle\WebTestCaseTrait;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use PHPUnit\Framework\Attributes\Depends;
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

        $this->client->request(
            'GET',
            \sprintf('/admin/app/user/%s/unlock', $user->getId())
        );

        static::assertResponseStatusCodeSame(302);
        static::assertResponseHeaderSame(
            'Location',
            \sprintf('/admin/app/user/%s/show', $user->getId())
        );

        $this->client->followRedirect();

        static::assertResponseIsSuccessful();

        static::assertSelectorTextContains('.alert-success', 'The user lock has been unlock for the next 24 hour');

        $userLock = $this->entityManager->find(
            UserLock::class,
            $userLock->getId()
        );

        static::assertEqualsWithDelta(new \DateTimeImmutable('+ 24 hours'), $userLock->getUnlockUntil(), 2);
    }

    #[Depends('testUnlock')]
    public function testNoAccess(): void
    {
        $this->login('locked@example.com');

        $user = $this->getUser('admin@example.com');

        $this->client->request(
            'GET',
            \sprintf('/admin/app/user/%s/unlock', $user->getId())
        );

        static::assertResponseStatusCodeSame(403);
    }
}
