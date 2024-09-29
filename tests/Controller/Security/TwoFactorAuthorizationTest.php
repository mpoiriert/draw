<?php

namespace App\Tests\Controller\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Bundle\TesterBundle\WebTestCase;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

class TwoFactorAuthorizationTest extends WebTestCase implements AutowiredInterface
{
    final public const ADMIN_URL = '/admin';

    #[AutowireClient]
    private KernelBrowser $client;

    #[AutowireService]
    private EntityManagerInterface $entityManager;

    private static User $user;

    #[DoesNotPerformAssertions]
    public function testCreateUser(): void
    {
        $user = new User();
        $user->setEmail('test-2fa@example.com');
        $user->setPlainPassword('test');
        $user->setRoles(['ROLE_ADMIN']);

        // This role for enabling 2fa as per configuration
        $user->enableTwoFActorAuthenticationProvider('totp');

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        self::$user = $user;
    }

    #[Depends('testCreateUser')]
    public function testLoginRedirectEnable2fa(): void
    {
        static::assertTrue(self::$user->needToEnableTotpAuthenticationEnabled());

        $this->client->followRedirects();

        $crawler = $this->loginToAdmin();

        static::assertStringContainsString(
            '/admin/app/user/'.self::$user->getId().'/enable-2fa',
            $crawler->getUri(),
            'User must be redirect to enable 2fa url'
        );
    }

    #[Depends('testCreateUser')]
    public function testCancel(): void
    {
        $this->client->followRedirects();

        $crawler = $this->loginToAdmin();

        $this->client->submit(
            $crawler->selectButton('Cancel')
                ->form()
        );

        $this->reloadUser();

        static::assertSame([], self::$user->getTwoFactorAuthenticationEnabledProviders());
    }

    public function testEnable2faInAdminInvalidCode(): void
    {
        $this->client->followRedirects();

        $this->reloadUser();

        // This role for enabling 2fa as per configuration
        self::$user->setRoles(['ROLE_2FA_ADMIN']);
        $this->entityManager->flush();

        $this->loginToAdmin();

        $crawler = $this->client->submit(
            $this->client->getCrawler()
                ->selectButton('Enable')
                ->form(['enable2fa_form[code]' => '111111'], 'POST')
        );

        static::assertStringContainsString('/enable-2fa', $crawler->getUri());

        static::assertStringContainsString(
            'Invalid code provided',
            static::getResponseContent()
        );
    }

    public function testEnable2faInAdmin(): void
    {
        $this->client->followRedirects();

        $this->loginToAdmin();

        $crawler = $this->client->submit(
            $this->client->getCrawler()
                ->selectButton('Enable')
                ->form(['enable2fa_form[code]' => '123456'], 'POST')
        );

        static::assertStringContainsString('/admin/dashboard', $crawler->getUri());

        static::assertStringContainsString(
            '2FA successfully enabled.',
            static::getResponseContent()
        );

        $this->reloadUser();

        static::assertTrue(self::$user->isTotpAuthenticationEnabled());
    }

    #[Depends('testEnable2faInAdmin')]
    public function test2faLoginToAdminFailed(): void
    {
        $this->client->followRedirects();

        $crawler = $this->client->submit(
            $this->loginToAdmin()
                ->selectButton('Login')
                ->form(['_auth_code' => '11111'], 'POST')
        );

        static::assertStringContainsString(
            'The verification code is not valid.',
            static::getResponseContent()
        );

        static::assertStringContainsString('/2fa', $crawler->getUri());
    }

    #[Depends('testEnable2faInAdmin')]
    public function test2faLoginToAdmin(): void
    {
        $this->client->followRedirects();

        $this->loginToAdmin();

        $crawler = $this->client->submit(
            $this->client->getCrawler()
                ->selectButton('Login')
                ->form(['_auth_code' => '123456'], 'POST')
        );

        static::assertStringContainsString('/admin/dashboard', $crawler->getUri());
    }

    #[Depends('testEnable2faInAdmin')]
    public function testDisable2faInAdmin(): void
    {
        $this->client->followRedirects();

        // This is to remove the force enable 2fa
        $user = $this->reloadUser();
        $user->setRoles(['ROLE_ADMIN']);
        $user->setForceEnablingTwoFactorAuthentication(false);

        $this->entityManager->flush();

        $this->client->submit(
            $this->loginToAdmin()
                ->selectButton('Login')
                ->form(['_auth_code' => '123456'], 'POST')
        );

        $crawler = $this->client->request(
            'GET',
            \sprintf(self::ADMIN_URL.'/app/user/%s/disable-2fa', self::$user->getId())
        );

        static::assertStringContainsString('/edit', $crawler->getUri());

        static::assertStringContainsString('2FA successfully disabled.', static::getResponseContent());

        static::assertFalse($this->reloadUser()->isTotpAuthenticationEnabled());
    }

    private function reloadUser(): User
    {
        self::$user = $this->entityManager->find(User::class, self::$user->getId());
        $this->entityManager->refresh(self::$user);

        return self::$user;
    }

    private function loginToAdmin(): Crawler
    {
        $this->client->request('GET', self::ADMIN_URL.'/logout');
        $crawler = $this->client->request('GET', self::ADMIN_URL.'/login');

        return $this->client->submit(
            $crawler
                ->selectButton('Login')
                ->form(
                    [
                        'admin_login_form[email]' => self::$user->getEmail(),
                        'admin_login_form[password]' => 'test',
                    ],
                    'POST'
                )
        );
    }
}
