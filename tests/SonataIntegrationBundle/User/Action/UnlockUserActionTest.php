<?php

namespace App\Tests\SonataIntegrationBundle\User\Action;

use App\Entity\User;
use App\Test\PHPUnit\Extension\SetUpAutowire\AutowireAdminUser;
use App\Test\TestKernelBrowser;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireEntity;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UnlockUserActionTest extends WebTestCase implements AutowiredInterface
{
    #[AutowireClient]
    private TestKernelBrowser $client;

    #[AutowireService]
    private EntityManagerInterface $entityManager;

    #[AutowireAdminUser]
    private User $admin;

    #[AutowireEntity(['email' => 'locked@example.com'])]
    private User $lockedUser;

    public function testUnlock(): void
    {
        $this->client->loginUserInAdmin($this->admin);

        $userLock = $this->lockedUser->getLocks()['manual-lock'];

        $userLock->setUnlockUntil(null);

        $this->client->request(
            'GET',
            \sprintf('/admin/app/user/%s/unlock', $this->lockedUser->getId())
        );

        static::assertResponseStatusCodeSame(Response::HTTP_FOUND);

        static::assertResponseHeaderSame(
            'Location',
            \sprintf('/admin/app/user/%s/show', $this->lockedUser->getId())
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
        $this->client->loginUserInAdmin($this->lockedUser);

        $this->client->request(
            'GET',
            \sprintf('/admin/app/user/%s/unlock', $this->admin->getId())
        );

        static::assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }
}
