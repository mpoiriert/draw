<?php

namespace App\Tests\OpenApi\EventListener;

use Draw\Bundle\TesterBundle\EventDispatcher\EventListenerTestTrait;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\OpenApi\Event\PreDumpRootSchemaEvent;
use Draw\Component\OpenApi\EventListener\ResponseApiExceptionListener;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class ResponseApiExceptionListenerTest extends KernelTestCase implements AutowiredInterface
{
    use EventListenerTestTrait;

    #[AutowireService]
    private ResponseApiExceptionListener $object;

    public function test(): void
    {
        $this->assertEventListenersRegistered(
            $this->object::class,
            [
                'kernel.exception' => ['onKernelException'],
                PreDumpRootSchemaEvent::class => ['addErrorDefinition'],
            ]
        );
    }
}
