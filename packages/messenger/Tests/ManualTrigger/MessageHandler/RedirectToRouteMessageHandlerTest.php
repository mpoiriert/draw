<?php

namespace Draw\Component\Messenger\Tests\ManualTrigger\MessageHandler;

use Draw\Component\Messenger\ManualTrigger\Message\RedirectToRouteMessageInterface;
use Draw\Component\Messenger\ManualTrigger\MessageHandler\RedirectToRouteMessageHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(RedirectToRouteMessageHandler::class)]
class RedirectToRouteMessageHandlerTest extends TestCase
{
    private RedirectToRouteMessageHandler $service;

    private UrlGeneratorInterface $urlGenerator;

    protected function setUp(): void
    {
        $this->service = new RedirectToRouteMessageHandler(
            $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class)
        );
    }

    public function testInvoke(): void
    {
        $message = $this->createMock(RedirectToRouteMessageInterface::class);

        $message
            ->expects(static::once())
            ->method('getRedirectResponse')
            ->with($this->urlGenerator)
            ->willReturn($response = new RedirectResponse('/'));

        static::assertSame(
            $response,
            $this->service->handleRedirectToRouteMessage($message)
        );
    }
}
