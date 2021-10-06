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

    public function setUp(): void
    {
        self::$entityManager = static::getService(EntityManagerInterface::class);
        $this->cleanUp();

        self::$user = $this->createTestUser();
    }

    public function tearDown(): void
    {
        $this->cleanUp();
    }

    private function cleanUp(): void
    {
        self::$entityManager->createQueryBuilder()
            ->delete(User::class, 'user')
            ->andWhere('user.email = :email')->setParameter('email', 'test-2fa@example.com')
            ->getQuery()->execute();
    }

    public function testEnable2faInAdmin(): void
    {
        /** @var KernelBrowser $client */
        $client = $this->getService('test.client');
        $client->followRedirects(true);
        $this->loginToAdmin($client, 'admin@example.com', 'admin');

        $crawler = $client->request(
            'GET',
            sprintf(self::ADMIN_URL.'/app/user/%s/enable-2fa', self::$user->getId())
        );
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $form = $crawler
            ->selectButton('Enable')
            ->form(['enable2fa_form[code]' => '123456'], 'POST');
        $crawler = $client->submit($form);

        $this->assertContains('/edit', $crawler->getUri());
        $this->assertContains('2FA was enabled', $client->getResponse()->getContent());

        $user = self::$entityManager->find(User::class, self::$user->getId());
        $this->assertTrue($user->isTotpAuthenticationEnabled());
    }

    public function testEnable2faInAdminInvalidCode(): void
    {
        /** @var KernelBrowser $client */
        $client = $this->getService('test.client');
        $client->followRedirects(true);
        $this->loginToAdmin($client, 'admin@example.com', 'admin');

        $crawler = $client->request(
            'GET',
            sprintf(self::ADMIN_URL.'/app/user/%s/enable-2fa', self::$user->getId())
        );
        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $form = $crawler
            ->selectButton('Enable')
            ->form(['enable2fa_form[code]' => '111111'], 'POST');
        $crawler = $client->submit($form);

        $this->assertContains('/enable-2fa', $crawler->getUri());
        $this->assertContains('Invalid code provided', $client->getResponse()->getContent());
    }

    public function testDisable2faInAdmin(): void
    {
        self::$user->setTotpSecret('TEST_SECRET');
        self::$entityManager->flush();
        $this->assertTrue(self::$user->isTotpAuthenticationEnabled());

        /** @var KernelBrowser $client */
        $client = $this->getService('test.client');
        $client->followRedirects(true);
        $this->loginToAdmin($client, 'admin@example.com', 'admin');

        $crawler = $client->request(
            'GET',
            sprintf(self::ADMIN_URL.'/app/user/%s/disable-2fa', self::$user->getId())
        );

        $this->assertContains('/edit', $crawler->getUri());
        $this->assertContains('2FA was disabled', $client->getResponse()->getContent());

        $user = self::$entityManager->find(User::class, self::$user->getId());
        $this->assertFalse($user->isTotpAuthenticationEnabled());
    }

    public function test2faLoginToAdmin(): void
    {
        self::$user->setTotpSecret('TEST_SECRET');
        self::$entityManager->flush();

        /** @var KernelBrowser $client */
        $client = $this->getService('test.client');
        $client->followRedirects(true);

        $this->loginToAdmin($client, 'test-2fa@example.com', 'test', false);
        $crawler = $client->request('GET', self::ADMIN_URL.'/2fa');
        $form = $crawler
            ->selectButton('Login')
            ->form(['_auth_code' => '123456'], 'POST');
        $crawler = $client->submit($form);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('/admin/dashboard', $crawler->getUri());
    }

    public function test2faLoginToAdminFailed(): void
    {
        self::$user->setTotpSecret('TEST_SECRET');
        self::$entityManager->flush();

        /** @var KernelBrowser $client */
        $client = $this->getService('test.client');
        $client->followRedirects(true);

        $this->loginToAdmin($client, 'test-2fa@example.com', 'test', false);
        $crawler = $client->request('GET', self::ADMIN_URL.'/2fa');
        $form = $crawler
            ->selectButton('Login')
            ->form(['_auth_code' => '11111'], 'POST');
        $crawler = $client->submit($form);

        $this->assertSame(200, $client->getResponse()->getStatusCode());
        $this->assertContains('/2fa', $crawler->getUri());
    }

    public function loginToAdmin(
        KernelBrowser $client,
        string $username,
        string $password,
        bool $validateSuccess = true
    ): ?Crawler {
        if (!$followingRedirects = $client->isFollowingRedirects()) {
            $client->followRedirects(true);
        }

        $client->request('GET', self::ADMIN_URL.'/logout');
        $crawler = $client->request('GET', self::ADMIN_URL.'/login');

        $form = $crawler
            ->selectButton('Login')
            ->form(
                [
                    'admin_login_form[email]' => $username,
                    'admin_login_form[password]' => $password,
                ],
                'POST'
            );

        $crawler = $client->submit($form);

        if ($validateSuccess) {
            $this->assertSame(200, $client->getResponse()->getStatusCode());
            // Make sure the user is logged in
            $this->assertContains('/admin/dashboard', $crawler->getUri());
        }

        $client->followRedirects($followingRedirects);

        return $crawler;
    }

    private function createTestUser(): User
    {
        $user = new User();
        $user->setEmail('test-2fa@example.com');
        $user->setPlainPassword('test');
        $user->setRoles(['ROLE_ADMIN']);

        self::$entityManager->persist($user);
        self::$entityManager->flush();

        return $user;
    }
}
