<?php

namespace App\Tests;

use Draw\Bundle\TesterBundle\EventDispatcher\EventDispatcherTesterTrait;
use Draw\Bundle\TesterBundle\Messenger\MessageHandlerTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @internal
 */
class AppKernelTest extends KernelTestCase
{
    use EventDispatcherTesterTrait;
    use MessageHandlerTesterTrait;

    private bool $resetFile = false;

    protected static function createKernel(array $options = []): KernelInterface
    {
        $options['debug'] = false;

        return parent::createKernel($options);
    }

    public function testEventDispatcherConfiguration(): void
    {
        $path = __DIR__.'/fixtures/AppKernelTest/testEventDispatcherConfiguration/event_dispatcher.xml';

        if ($this->resetFile && file_exists($path)) {
            unlink($path);
        }

        $this->assertEventDispatcherConfiguration($path);
    }

    public function testMessageHandlerConfiguration(): void
    {
        $path = __DIR__.'/fixtures/AppKernelTest/testMessageHandlerConfiguration/message_handler.xml';

        if ($this->resetFile && file_exists($path)) {
            unlink($path);
        }

        $this->assertMessageHandlerConfiguration($path);
    }
}
