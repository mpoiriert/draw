<?php

declare(strict_types=1);

namespace App\Tests\SonataIntegrationBundle;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait WebTestCaseTrait
{
    #[AutowireService]
    protected EntityManagerInterface $entityManager;

    #[AutowireClient]
    private KernelBrowser $client;

    protected function getUser(string $email): User
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['email' => $email]);
    }

    protected function login(string $email): void
    {
        $this->client->loginUser(
            $this->getUser($email),
            'user'
        );
    }
}
