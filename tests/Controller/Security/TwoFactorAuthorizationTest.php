<?php

namespace App\Tests\Controller\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Bundle\TesterBundle\WebTestCase;
use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

class TwoFactorAuthorizationTest extends WebTestCase implements AutowiredInterface
{
    final public const ADMIN_URL = '/admin';

    #[AutowireClient]
    private KernelBrowser $client;

    private static EntityManagerInterface $entityManager;

    private static User $user;

    public static function setUpBeforeClass(): void
    {
        self::$entityManager = static::getContainer()->get(EntityManagerInterface::class);

        self::$user = (new User())
            ->setEmail('test-2fa@example.com')
            ->setPlainPassword('test')
            ->setRoles(['ROLE_ADMIN']);

        // This role for enabling 2fa as per configuration
        self::$user->enableTwoFActorAuthenticationProvider('totp');

        self::$entityManager->persist(self::$user);
        self::$entityManager->flush();

        static::ensureKernelShutdown();
    }

    #[
        BeforeClass,
        AfterClass
    ]
    public static function cleanUp(): void
    {
        static::getContainer()
            ->get(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->delete(User::class, 'user')
            ->andWhere('user.email like :email')
            ->setParameter('email', 'test-2fa%@example.com')
            ->getQuery()
            ->execute();
    }

    public function testLoginRedirectEnable2fa(): KernelBrowser
    {
        static::assertTrue(self::$user->needToEnableTotpAuthenticationEnabled());

        $this->client->followRedirects();

        $crawler = $this->loginToAdmin($this->client, self::$user->getUsername(), 'test');

        static::assertStringContainsString(
            '/admin/app/user/'.self::$user->getId().'/enable-2fa',
            $crawler->getUri(),
            'User must be redirect to enable 2fa url'
        );

        return $this->client;
    }

    #[Depends('testLoginRedirectEnable2fa')]
    public function testCancel(KernelBrowser $client): KernelBrowser
    {
        $client->followRedirects();

        $crawler = $client->request('GET', '/admin/app/user/'.self::$user->getId().'/enable-2fa');

        $client->submit(
            $crawler->selectButton('Cancel')
                ->form()
        );

        $this->reloadUser();

        static::assertSame([], self::$user->getTwoFactorAuthenticationEnabledProviders());

        return $client;
    }

    #[Depends('testLoginRedirectEnable2fa')]
    public function testEnable2faInAdminInvalidCode(KernelBrowser $client): KernelBrowser
    {
        $this->reloadUser();

        // This role for enabling 2fa as per configuration
        self::$user->setRoles(['ROLE_2FA_ADMIN']);
        self::$entityManager->flush();

        $this->loginToAdmin($client, self::$user->getUsername(), 'test');

        $crawler = $client->submit(
            $client->getCrawler()
                ->selectButton('Enable')
                ->form(['enable2fa_form[code]' => '111111'], 'POST')
        );

        static::assertStringContainsString('/enable-2fa', $crawler->getUri());
        static::assertStringContainsString('Invalid code provided', $client->getResponse()->getContent());

        return $client;
    }

    #[Depends('testEnable2faInAdminInvalidCode')]
    public function testEnable2faInAdmin(KernelBrowser $client): KernelBrowser
    {
        $crawler = $client->submit(
            $client->getCrawler()
                ->selectButton('Enable')
                ->form(['enable2fa_form[code]' => '123456'], 'POST')
        );

        static::assertStringContainsString('/admin/dashboard', $crawler->getUri());
        static::assertStringContainsString('2FA successfully enabled.', $client->getResponse()->getContent());

        $this->reloadUser();
        static::assertTrue(self::$user->isTotpAuthenticationEnabled());

        return $client;
    }

    #[Depends('testEnable2faInAdmin')]
    public function test2faLoginToAdminFailed(KernelBrowser $client): KernelBrowser
    {
        $crawler = $client->submit(
            $this->loginToAdmin($client, self::$user->getUsername(), 'test')
                ->selectButton('Login')
                ->form(['_auth_code' => '11111'], 'POST')
        );

        static::assertStringContainsString('The verification code is not valid.', $client->getResponse()->getContent());
        static::assertStringContainsString('/2fa', $crawler->getUri());

        return $client;
    }

    #[Depends('test2faLoginToAdminFailed')]
    public function test2faLoginToAdmin(KernelBrowser $client): KernelBrowser
    {
        $crawler = $client->submit(
            $client->getCrawler()
                ->selectButton('Login')
                ->form(['_auth_code' => '123456'], 'POST')
        );

        static::assertStringContainsString('/admin/dashboard', $crawler->getUri());

        return $client;
    }

    #[Depends('test2faLoginToAdmin')]
    public function testDisable2faInAdmin(KernelBrowser $client): KernelBrowser
    {
        // This is to remove the force enable 2fa
        $user = $this->reloadUser();
        $user->setRoles(['ROLE_ADMIN']);
        $user->setForceEnablingTwoFactorAuthentication(false);

        self::$entityManager->flush();

        $client->submit(
            $this->loginToAdmin($client, self::$user->getUsername(), 'test')
                ->selectButton('Login')
                ->form(['_auth_code' => '123456'], 'POST')
        );

        $crawler = $client->request(
            'GET',
            sprintf(self::ADMIN_URL.'/app/user/%s/disable-2fa', self::$user->getId())
        );

        static::assertStringContainsString('/edit', $crawler->getUri());
        static::assertStringContainsString('2FA successfully disabled.', $client->getResponse()->getContent());

        static::assertFalse($this->reloadUser()->isTotpAuthenticationEnabled());

        return $client;
    }

    private function reloadUser(): User
    {
        self::$user = self::$entityManager->find(User::class, self::$user->getId());
        self::$entityManager->refresh(self::$user);

        return self::$user;
    }

    private function loginToAdmin(
        KernelBrowser $client,
        string $username,
        string $password
    ): Crawler {
        $client->request('GET', self::ADMIN_URL.'/logout');
        $crawler = $client->request('GET', self::ADMIN_URL.'/login');

        return $client->submit(
            $crawler
                ->selectButton('Login')
                ->form(
                    [
                        'admin_login_form[email]' => $username,
                        'admin_login_form[password]' => $password,
                    ],
                    'POST'
                )
        );
    }
}
