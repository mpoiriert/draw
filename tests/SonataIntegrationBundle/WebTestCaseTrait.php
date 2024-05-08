<?php

declare(strict_types=1);

namespace App\Tests\SonataIntegrationBundle;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @method static KernelBrowser createClient(array $options = [], array $server = [])
 */
trait WebTestCaseTrait
{
    protected static ?KernelBrowser $client = null;

    #[AutowireService]
    protected EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        static::$client = static::createClient();
    }

    protected function getUser(string $email): User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    protected function login(string $email): void
    {
        static::$client->loginUser(
            $this->getUser($email),
            'user'
        );
    }
}
