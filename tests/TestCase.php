<?php

namespace App\Tests;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\TesterBundle\DependencyInjection\ServiceTesterTrait;
use Draw\Bundle\TesterBundle\Http\BrowserFactoryInterface;
use Draw\Bundle\TesterBundle\Http\HttpTesterTrait;
use Draw\Bundle\TesterBundle\Profiling\MetricTesterTrait;
use Draw\Bundle\UserBundle\Jwt\JwtAuthenticator;
use Draw\Component\Tester\Http\ClientInterface;
use Draw\Component\Tester\Http\Request\DefaultValueObserver;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class TestCase extends KernelTestCase implements BrowserFactoryInterface
{
    use ServiceTesterTrait;
    use HttpTesterTrait {
        createHttpTesterClient as defaultCreateHttpTesterClient;
    }
    use MetricTesterTrait;

    public function createBrowser(): AbstractBrowser
    {
        return static::bootKernel()->getContainer()->get('test.client');
    }

    public function createHttpTesterClient(): ClientInterface
    {
        $client = $this->defaultCreateHttpTesterClient();
        $client->registerObserver(
            new DefaultValueObserver([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])
        );

        return $client;
    }

    private $connectionTokens = [];

    public function getConnectionToken($email): string
    {
        if (!isset($this->connectionTokens[$email])) {
            $user = $this->getService(EntityManagerInterface::class)
                ->getRepository(User::class)
                ->findOneBy(['email' => $email]);
            if (is_null($user)) {
                throw new \InvalidArgumentException('User with email ['.$email.'] not found.');
            }

            $this->connectionTokens[$email] = $this->getService(JwtAuthenticator::class)->encode($user);
        }

        return $this->connectionTokens[$email];
    }

    public function connect($withEmail = 'admin@example.com')
    {
        $this->httpTester()
            ->registerObserver(
            new DefaultValueObserver([
                'Authorization' => 'Bearer '.self::getConnectionToken($withEmail),
            ])
        );
    }
}
