<?php

namespace App\Tests;

use Draw\Bundle\TesterBundle\EventDispatcher\EventDispatcherTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class AppKernelTest extends KernelTestCase
{
    use EventDispatcherTesterTrait;

    private bool $resetFile = false;

    public function testEventDispatcherConfiguration(): void
    {
        $path = __DIR__.'/fixtures/AppKernelTest/testEventDispatcherConfiguration/event_dispatcher.xml';

        if ($this->resetFile && file_exists($path)) {
            unlink($path);
        }

        $this->assertEventDispatcherConfiguration($path);
    }
}
