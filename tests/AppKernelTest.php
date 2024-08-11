<?php

namespace App\Tests;

use Draw\Bundle\TesterBundle\EventDispatcher\EventDispatcherTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AppKernelTest extends KernelTestCase
{
    use EventDispatcherTesterTrait;

    public function testEventDispatcherConfiguration(): void
    {
        $this->assertEventDispatcherConfiguration(
            __DIR__.'/fixtures/AppKernelTest/testEventDispatcherConfiguration/event_dispatcher.xml'
        );
    }
}
