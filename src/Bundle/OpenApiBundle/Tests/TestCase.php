<?php namespace Draw\Bundle\OpenApiBundle\Tests;

use Draw\Bundle\TesterBundle\DependencyInjection\ServiceTesterTrait;
use Draw\Bundle\TesterBundle\Http\BrowserFactoryInterface;
use Draw\Bundle\TesterBundle\Http\HttpTesterTrait;
use Draw\Component\Tester\Http\ClientInterface;
use Draw\Component\Tester\Http\Observer\DefaultBaseUriObserver;
use Draw\Component\Tester\Http\Request\DefaultValueObserver;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class TestCase extends KernelTestCase implements BrowserFactoryInterface
{
    protected static $class = AppKernel::class;

    use ServiceTesterTrait;
    use HttpTesterTrait {
        createHttpTesterClient as defaultCreateHttpTesterClient;
    }

    public function createHttpTesterClient(): ClientInterface
    {
        $client = $this->defaultCreateHttpTesterClient();
        $client->registerObserver(
            new DefaultValueObserver([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ])
        );

        return $client;
    }

    public function createBrowser(): AbstractBrowser
    {
        return static::bootKernel()->getContainer()->get('test.client');
    }
}