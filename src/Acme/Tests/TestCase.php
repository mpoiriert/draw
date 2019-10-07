<?php namespace Draw\Acme\Tests;

use Draw\Bundle\TesterBundle\Http\BrowserFactoryInterface;
use Draw\Bundle\TesterBundle\Http\HttpTesterTrait;
use Draw\Bundle\TesterBundle\Profiling\MetricTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class TestCase extends KernelTestCase implements BrowserFactoryInterface
{
    use HttpTesterTrait;
    use MetricTesterTrait;

    public function createBrowser(): AbstractBrowser
    {
        return static::bootKernel()->getContainer()->get('test.client');
    }
}