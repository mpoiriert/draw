<?php

namespace App\Tests;

use Draw\Bundle\TesterBundle\DependencyInjection\ServiceTesterTrait;
use Draw\Bundle\TesterBundle\Http\BrowserFactoryInterface;
use Draw\Bundle\TesterBundle\Http\HttpTesterTrait;
use Draw\Bundle\TesterBundle\Messenger\MessengerTesterTrait;
use Draw\Bundle\TesterBundle\Profiling\MetricTesterTrait;
use Draw\Component\Tester\Http\ClientInterface;
use Draw\Component\Tester\Http\Request\DefaultValueObserver;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class TestCase extends KernelTestCase implements BrowserFactoryInterface
{
    use AuthenticatorTestTrait;
    use HttpTesterTrait {
        createHttpTesterClient as defaultCreateHttpTesterClient;
    }
    use MessengerTesterTrait;
    use MetricTesterTrait;
    use ServiceTesterTrait;

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
}
