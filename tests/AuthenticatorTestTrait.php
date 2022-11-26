<?php

namespace App\Tests;

use App\Entity\User;
use Draw\Component\Security\Http\Authenticator\JwtAuthenticator;
use Draw\Component\Tester\Http\ClientInterface;
use Draw\Component\Tester\Http\Request\DefaultValueObserver;
use Draw\DoctrineExtra\ORM\EntityHandler;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait AuthenticatorTestTrait
{
    /**
     * @var array<string,string>
     */
    private array $connectionTokens = [];

    public function getConnectionToken(string $email): string
    {
        if (!isset($this->connectionTokens[$email])) {
            $user = static::getContainer()
                ->get(EntityHandler::class)
                ->findOneBy(User::class, ['email' => $email]);

            if (null === $user) {
                throw new \InvalidArgumentException('User with email ['.$email.'] not found.');
            }

            $this->connectionTokens[$email] = static::getContainer()->get(JwtAuthenticator::class)->generaToken($user);
        }

        return $this->connectionTokens[$email];
    }

    public function setAuthorizationHeader(KernelBrowser $client, string $withEmail): void
    {
        $client->setServerParameter('HTTP_Authorization', 'Bearer '.self::getConnectionToken($withEmail));
    }

    public function connect(ClientInterface $client, string $withEmail = 'admin@example.com'): ClientInterface
    {
        $client
            ->registerObserver(
                new DefaultValueObserver([
                    'Authorization' => 'Bearer '.self::getConnectionToken($withEmail),
                ])
            );

        return $client;
    }
}
