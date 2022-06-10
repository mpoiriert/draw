<?php

namespace Draw\Component\Messenger\Tests\ManualTrigger\Message;

use Draw\Component\Messenger\ManualTrigger\Message\RedirectToRouteMessageTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @covers \Draw\Component\Messenger\ManualTrigger\Message\RedirectToRouteMessageTrait
 */
class RedirectToRouteMessageTraitTest extends TestCase
{
    use RedirectToRouteMessageTrait;

    public function testGetRedirectResponse(): void
    {
        $this->route = uniqid('route-');
        $this->urlParameters = [uniqid('key-') => uniqid('value-')];

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);

        $urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->route,
                $this->urlParameters,
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($url = uniqid('url-'));

        $response = $this->getRedirectResponse($urlGenerator);

        $this->assertInstanceOf(
            RedirectResponse::class,
            $response
        );

        $this->assertSame(
            $url,
            $response->getTargetUrl()
        );
    }
}
