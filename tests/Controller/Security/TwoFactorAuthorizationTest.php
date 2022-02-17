<?php

namespace App\Tests\Controller\Security;

use App\Entity\User;
use App\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;

class TwoFactorAuthorizationTest extends TestCase
{
    public const ADMIN_URL = '/admin';

    /**
     * @var EntityManagerInterface
     */
    private static $entityManager;

    private static $user;

    public static function setUpBeforeClass(): void
    {
        self::$entityManager = static::getService(EntityManagerInterface::class);

        $user = new User();
        $user->setEmail('test-2fa@example.com');
        $user->setPlainPassword('test');

        // This role for enabling 2fa as per configuration
        $user->setRoles(['ROLE_2FA_ADMIN']);

        self::$entityManager->persist($user);
        self::$entityManager->flush();

        self::$user = $user;
    }

    /**
     * @afterClass
     * @beforeClass
     */
    public static function cleanUp(): void
    {
        static::getService(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->delete(User::class, 'user')
            ->andWhere('user.email like :email')
            ->setParameter('email', 'test-2fa%@example.com')
            ->getQuery()
            ->execute();
    }

    public function testLoginRedirectEnable2fa(): KernelBrowser
    {
        $this->assertTrue(self::$user->isForceEnablingTwoFactorAuthentication());
        /** @var KernelBrowser $client */
        $client = $this->getService('test.client');
        $client->followRedirects(true);

        $crawler = $this->loginToAdmin($client, static::$user->getUsername(), 'test');

        $this->assertStringContainsString(
            '/admin/app/user/'.self::$user->getId().'/enable-2fa',
            $crawler->getUri(),
            'User must be redirect to enable 2fa url'
        );

        return $client;
    }

    /**
     * @depends testLoginRedirectEnable2fa
     */
    public function testEnable2faInAdminInvalidCode(KernelBrowser $client): KernelBrowser
    {
        $crawler = $client->submit(
            $client->getCrawler()
                ->selectButton('Enable')
                ->form(['enable2fa_form[code]' => '111111'], 'POST')
        );

        $this->assertStringContainsString('/enable-2fa', $crawler->getUri());
        $this->assertStringContainsString('Invalid code provided', $client->getResponse()->getContent());

        return $client;
    }

    /**
     * @depends testEnable2faInAdminInvalidCode
     */
    public function testEnable2faInAdmin(KernelBrowser $client): KernelBrowser
    {
        $crawler = $client->submit(
            $client->getCrawler()
                ->selectButton('Enable')
                ->form(['enable2fa_form[code]' => '123456'], 'POST')
        );

        $this->assertStringContainsString('/admin/dashboard', $crawler->getUri());
        $this->assertStringContainsString('2FA successfully enabled.', $client->getResponse()->getContent());

        $user = self::$entityManager->find(User::class, self::$user->getId());
        $this->assertTrue($user->isTotpAuthenticationEnabled());

        return $client;
    }

    /**
     * @depends testEnable2faInAdmin
     */
    public function test2faLoginToAdminFailed(KernelBrowser $client): KernelBrowser
    {
        $crawler = $client->submit(
            $this->loginToAdmin($client, static::$user->getUsername(), 'test')
                ->selectButton('Login')
                ->form(['_auth_code' => '11111'], 'POST')
        );

        $this->assertStringContainsString('The verification code is not valid.', $client->getResponse()->getContent());
        $this->assertStringContainsString('/2fa', $crawler->getUri());

        return $client;
    }

    /**
     * @depends test2faLoginToAdminFailed
     */
    public function test2faLoginToAdmin(KernelBrowser $client): KernelBrowser
    {
        $crawler = $client->submit(
            $client->getCrawler()
                ->selectButton('Login')
                ->form(['_auth_code' => '123456'], 'POST')
        );

        $this->assertStringContainsString('/admin/dashboard', $crawler->getUri());

        return $client;
    }

    /**
     * @depends test2faLoginToAdmin
     */
    public function testDisable2faInAdmin(KernelBrowser $client): KernelBrowser
    {
        // This is to remove the force enable 2fa
        $user = $this->reloadUser();
        $user->setRoles(['ROLE_ADMIN']);
        $user->setForceEnablingTwoFactorAuthentication(false);

        static::$entityManager->flush();

        $client->submit(
            $this->loginToAdmin($client, static::$user->getUsername(), 'test')
                ->selectButton('Login')
                ->form(['_auth_code' => '123456'], 'POST')
        );

        $crawler = $client->request(
            'GET',
            sprintf(self::ADMIN_URL.'/app/user/%s/disable-2fa', self::$user->getId())
        );

        $this->assertStringContainsString('/edit', $crawler->getUri());
        $this->assertStringContainsString('2FA successfully disabled.', $client->getResponse()->getContent());

        $this->assertFalse($this->reloadUser()->isTotpAuthenticationEnabled());

        return $client;
    }

    private function reloadUser(): User
    {
        static::$user = static::$entityManager->find(User::class, static::$user->getId());
        static::$entityManager->refresh(static::$user);

        return static::$user;
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
