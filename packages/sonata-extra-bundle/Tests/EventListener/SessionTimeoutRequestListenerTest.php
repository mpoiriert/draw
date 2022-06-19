<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\EventListener;

use Draw\Bundle\SonataExtraBundle\EventListener\SessionTimeoutRequestListener;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @covers \Draw\Bundle\SonataExtraBundle\EventListener\SessionTimeoutRequestListener
 */
class SessionTimeoutRequestListenerTest extends TestCase
{
    private SessionTimeoutRequestListener $object;

    /**
     * @var MockObject|UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * @var MockObject|Security
     */
    private Security $security;

    protected function setUp(): void
    {
        $this->object = new SessionTimeoutRequestListener(
            $this->security = $this->createMock(Security::class),
            $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class),
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        static::assertSame(
            [
                RequestEvent::class => [
                    ['onKernelRequestInvalidate', 9],
                ],
                ResponseEvent::class => [
                    ['onKernelResponseSetLastUsed', 0],
                    ['onKernelResponseAddDialog', -2000],
                ],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    public function testOnKernelRequestInvalidate(): void
    {
        $requestEvent = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST
        );

        $request->setSession($session = $this->createMock(Session::class));

        $session->expects(static::once())
            ->method('get')
            ->with('draw_sonata_integration_last_used')
            ->willReturn(time() - 3601);

        $session->expects(static::once())
            ->method('invalidate');

        $this->object->onKernelRequestInvalidate($requestEvent);
    }

    public function provideTestOnKernelRequestInvalidateNotInvalidate(): iterable
    {
        $request = $this->createMock(Request::class);

        $request
            ->expects(static::any())
            ->method('hasSession')
            ->willReturn(true);

        $request
            ->expects(static::never())
            ->method('getSession');

        yield 'sub-request' => [
            $request,
            HttpKernelInterface::SUB_REQUEST,
        ];

        $session = $this->createMock(SessionInterface::class);

        $session
            ->expects(static::once())
            ->method('get')
            ->with('draw_sonata_integration_last_used')
            ->willReturn(time() - 60);

        $session
            ->expects(static::never())
            ->method('invalidate');

        $request = $this->createMock(Request::class);

        $request
            ->expects(static::once())
            ->method('hasSession')
            ->willReturn(true);

        $request
            ->expects(static::once())
            ->method('getSession')
            ->willReturn($session);

        yield 'no-expired' => [
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        ];

        $request = $this->createMock(Request::class);

        $request
            ->expects(static::once())
            ->method('hasSession')
            ->willReturn(false);

        $request
            ->expects(static::never())
            ->method('getSession');

        yield 'no-session' => [
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        ];
    }

    /**
     * @dataProvider provideTestOnKernelRequestInvalidateNotInvalidate
     */
    public function testOnKernelRequestInvalidateNotInvalidate(Request $request, int $requestType): void
    {
        $requestEvent = new RequestEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $requestType
        );

        $this->object->onKernelRequestInvalidate($requestEvent);
    }

    public function testOnKernelResponseSetLastUsed(): void
    {
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response()
        );

        $request->setSession($session = new Session(new MockArraySessionStorage()));

        $this->object->onKernelResponseSetLastUsed($event);

        static::assertEqualsWithDelta(
            time(),
            $session->get('draw_sonata_integration_last_used'),
            1
        );
    }

    public function provideTestOnKernelResponseSetLastUsedNoSet(): iterable
    {
        $request = $this->createMock(Request::class);

        $request
            ->expects(static::any())
            ->method('hasSession')
            ->willReturn(true);

        $request
            ->expects(static::never())
            ->method('getSession');

        yield 'sub-request' => [
            $request,
            HttpKernelInterface::SUB_REQUEST,
        ];

        $request = $this->createMock(Request::class);

        $request
            ->expects(static::once())
            ->method('hasSession')
            ->willReturn(false);

        $request
            ->expects(static::never())
            ->method('getSession');

        yield 'no-session' => [
            $request,
        ];
    }

    /**
     * @dataProvider provideTestOnKernelResponseSetLastUsedNoSet
     */
    public function testOnKernelResponseSetLastUsedNoSet(
        Request $request,
        int $requestType = HttpKernelInterface::MAIN_REQUEST
    ): void {
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            $requestType,
            new Response()
        );

        $this->object->onKernelResponseSetLastUsed($event);
    }

    public function testOnKernelResponseAddDialog(): void
    {
        $event = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $response = new Response()
        );

        $response->headers->set('Content-Type', 'text/html');

        $response->setContent('<meta data-sonata-admin/><title>value</title>');

        $this->security->expects(static::once())
            ->method('getUser')
            ->willReturn($this->createMock(UserInterface::class));

        $this->urlGenerator->expects(static::exactly(2))
            ->method('generate')
            ->withConsecutive(
                ['keep_alive'],
                ['admin_login']
            )
            ->willReturnOnConsecutiveCalls(
                '/admin/keep-alive',
                '/admin/login'
            );

        $this->object->onKernelResponseAddDialog($event);

        static::assertSame(
            '<meta data-sonata-admin/>
  <script type="text/javascript">
    const sessionHandler = new SessionExpirationHandler(3600,"/admin/keep-alive","/admin/login");
  </script>

  <title>value</title>',
            $response->getContent()
        );
    }

    public function provideTestOnKernelResponseAddDialogNoInjection(): iterable
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent('<meta data-sonata-admin/><title>value</title>');

        $user = $this->createMock(UserInterface::class);

        yield 'no-user' => [
            $response,
        ];

        yield 'sub-request' => [
            $response,
            $user,
            HttpKernelInterface::SUB_REQUEST,
        ];

        $newResponse = (clone $response);
        $newResponse->headers = new HeaderBag();
        yield 'not-html-content-type' => [
            $newResponse,
            $user,
        ];

        $newResponse = (clone $response);
        $newResponse->setContent(null);
        yield 'no-content' => [
            $newResponse,
            $user,
        ];

        $newResponse = (clone $response);
        $newResponse->setContent('<title>title</title>');
        yield 'content-not-sonata-admin' => [
            $newResponse,
            $user,
        ];

        $newResponse = (clone $response);
        $newResponse->setContent('<meta data-sonata-admin />');
        yield 'content-no-title' => [
            $newResponse,
            $user,
        ];
    }

    /**
     * @dataProvider provideTestOnKernelResponseAddDialogNoInjection
     */
    public function testOnKernelResponseAddDialogNoInjection(
        Response $response,
        ?UserInterface $user = null,
        int $requestType = HttpKernelInterface::MAIN_REQUEST
    ): void {
        $this->security->expects(static::once())
            ->method('getUser')
            ->willReturn($user);

        $this->urlGenerator->expects(static::never())
            ->method('generate');

        $previousContent = $response->getContent();

        $this->object->onKernelResponseAddDialog(
            new ResponseEvent(
                $this->createMock(HttpKernelInterface::class),
                new Request(),
                $requestType,
                $response
            )
        );

        static::assertSame(
            $previousContent,
            $response->getContent()
        );
    }
}
