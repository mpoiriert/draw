<?php

namespace Draw\Component\Messenger\Tests\ManualTrigger\MessageHandler;

use Draw\Component\Messenger\ManualTrigger\Message\RedirectToRouteMessageInterface;
use Draw\Component\Messenger\ManualTrigger\MessageHandler\RedirectToRouteMessageHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @internal
 */
#[CoversClass(RedirectToRouteMessageHandler::class)]
class RedirectToRouteMessageHandlerTest extends TestCase
{
    private RedirectToRouteMessageHandler $service;

    private UrlGeneratorInterface&Stub $urlGenerator;

    protected function setUp(): void
    {
        $this->service = new RedirectToRouteMessageHandler(
            $this->urlGenerator = $this->createStub(UrlGeneratorInterface::class)
        );
    }

    public function testInvoke(): void
    {
        $message = $this->createMock(RedirectToRouteMessageInterface::class);

        $message
            ->expects($this->once())
            ->method('getRedirectResponse')
            ->with($this->urlGenerator)
            ->willReturn($response = new RedirectResponse('/'))
        ;

        $this->assertSame(
            $response,
            $this->service->handleRedirectToRouteMessage($message)
        );
    }
}
