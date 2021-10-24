<?php

namespace Draw\Bundle\DashboardBundle\Tests\Controller;

use Draw\Bundle\TesterBundle\Http\BrowserFactoryInterface;
use Draw\Bundle\TesterBundle\Http\HttpTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

class OptionsControllerTest extends KernelTestCase implements BrowserFactoryInterface
{
    use HttpTesterTrait;

    public function createBrowser(): AbstractBrowser
    {
        return static::bootKernel()->getContainer()->get('test.client');
    }

    public function test()
    {
        $this->httpTester()
            ->options('/api/users')
            ->assertStatus(200);
    }
}
