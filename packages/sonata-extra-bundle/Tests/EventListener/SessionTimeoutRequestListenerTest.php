<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\EventListener;

use Draw\Bundle\SonataExtraBundle\EventListener\SessionTimeoutRequestListener;
use Draw\Component\Security\Core\Security;
use Draw\Component\Tester\DoubleTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\DoesNotPerformAssertions;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @internal
 */
#[CoversClass(SessionTimeoutRequestListener::class)]
class SessionTimeoutRequestListenerTest extends TestCase
{
    use DoubleTrait;

    public function testGetSubscribedEvents(): void
    {
        $object = new SessionTimeoutRequestListener(
            static::createStub(Security::class),
            static::createStub(UrlGeneratorInterface::class),
        );

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
            $object::getSubscribedEvents()
        );
    }

    public function testOnKernelRequestInvalidate(): void
    {
        $object = new SessionTimeoutRequestListener(
            static::createStub(Security::class),
            static::createStub(UrlGeneratorInterface::class),
        );

        $requestEvent = new RequestEvent(
            static::createStub(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST
        );

        $request->setSession($session = $this->createMock(Session::class));

        $session->expects(static::once())
            ->method('get')
            ->with('draw_sonata_integration_last_used')
            ->willReturn(time() - 3601)
        ;

        $session->expects(static::once())
            ->method('invalidate')
        ;

        $object->onKernelRequestInvalidate($requestEvent);
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideOnKernelRequestInvalidateNotInvalidateCases')]
    public function testOnKernelRequestInvalidateNotInvalidate(Request $request, int $requestType): void
    {
        $object = new SessionTimeoutRequestListener(
            static::createStub(Security::class),
            static::createStub(UrlGeneratorInterface::class),
        );

        $requestEvent = new RequestEvent(
            static::createStub(HttpKernelInterface::class),
            $request,
            $requestType
        );

        $object->onKernelRequestInvalidate($requestEvent);
    }

    public static function provideOnKernelRequestInvalidateNotInvalidateCases(): iterable
    {
        $request = new class extends Request {
            public function hasSession($skipIfUninitialized = false): bool
            {
                return true;
            }

            public function getSession(): SessionInterface
            {
                throw new \RuntimeException('Should not be called');
            }
        };

        yield 'sub-request' => [
            $request,
            HttpKernelInterface::SUB_REQUEST,
        ];

        $request = new class extends Request {
            public function hasSession($skipIfUninitialized = false): bool
            {
                return true;
            }

            public function getSession(): SessionInterface
            {
                return new class extends Session {
                    public function get($name, $default = null): mixed
                    {
                        if ('draw_sonata_integration_last_used' === $name) {
                            return time() - 60;
                        }

                        throw new \RuntimeException('Should not be called');
                    }

                    public function invalidate(?int $lifetime = null): bool
                    {
                        throw new \RuntimeException('Should not be called');
                    }
                };
            }
        };

        yield 'no-expired' => [
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        ];

        $request = new class extends Request {
            public function hasSession($skipIfUninitialized = false): bool
            {
                return false;
            }

            public function getSession(): SessionInterface
            {
                throw new \RuntimeException('Should not be called');
            }
        };

        yield 'no-session' => [
            $request,
            HttpKernelInterface::MAIN_REQUEST,
        ];
    }

    public function testOnKernelResponseSetLastUsed(): void
    {
        $object = new SessionTimeoutRequestListener(
            static::createStub(Security::class),
            static::createStub(UrlGeneratorInterface::class),
        );

        $event = new ResponseEvent(
            static::createStub(HttpKernelInterface::class),
            $request = new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Response()
        );

        $request->setSession($session = new Session(new MockArraySessionStorage()));

        $object->onKernelResponseSetLastUsed($event);

        static::assertEqualsWithDelta(
            time(),
            $session->get('draw_sonata_integration_last_used'),
            1
        );
    }

    #[DoesNotPerformAssertions]
    #[DataProvider('provideOnKernelResponseSetLastUsedNoSetCases')]
    public function testOnKernelResponseSetLastUsedNoSet(
        Request $request,
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
    ): void {
        $object = new SessionTimeoutRequestListener(
            static::createStub(Security::class),
            static::createStub(UrlGeneratorInterface::class),
        );

        $event = new ResponseEvent(
            static::createStub(HttpKernelInterface::class),
            $request,
            $requestType,
            new Response()
        );

        $object->onKernelResponseSetLastUsed($event);
    }

    public static function provideOnKernelResponseSetLastUsedNoSetCases(): iterable
    {
        $request = new class extends Request {
            public function hasSession($skipIfUninitialized = false): bool
            {
                return true;
            }

            public function getSession(): SessionInterface
            {
                throw new \RuntimeException('Should not be called');
            }
        };

        yield 'sub-request' => [
            $request,
            HttpKernelInterface::SUB_REQUEST,
        ];

        $request = new class extends Request {
            public function hasSession($skipIfUninitialized = false): bool
            {
                return false;
            }

            public function getSession(): SessionInterface
            {
                throw new \RuntimeException('Should not be called');
            }
        };

        yield 'no-session' => [
            $request,
        ];
    }

    public function testOnKernelResponseAddDialog(): void
    {
        $object = new SessionTimeoutRequestListener(
            $security = $this->createMock(Security::class),
            $urlGenerator = $this->createMock(UrlGeneratorInterface::class),
        );

        $event = new ResponseEvent(
            static::createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $response = new Response()
        );

        $response->headers->set('Content-Type', 'text/html');

        $response->setContent('<meta data-sonata-admin/><title>value</title>');

        $security->expects(static::once())
            ->method('getUser')
            ->willReturn(static::createStub(UserInterface::class))
        ;

        $urlGenerator->expects(static::exactly(2))
            ->method('generate')
            ->with(
                ...static::withConsecutive(
                    ['keep_alive'],
                    ['admin_login']
                )
            )
            ->willReturnOnConsecutiveCalls(
                '/admin/keep-alive',
                '/admin/login'
            )
        ;

        $object->onKernelResponseAddDialog($event);

        static::assertSame(
            '<meta data-sonata-admin/>
  <script type="text/javascript">
    const sessionHandler = new SessionExpirationHandler(3600,"/admin/keep-alive","/admin/login");
  </script>

  <title>value</title>',
            $response->getContent()
        );
    }

    #[DataProvider('provideOnKernelResponseAddDialogNoInjectionCases')]
    public function testOnKernelResponseAddDialogNoInjection(
        Response $response,
        ?UserInterface $user = null,
        int $requestType = HttpKernelInterface::MAIN_REQUEST,
    ): void {
        $object = new SessionTimeoutRequestListener(
            $security = $this->createMock(Security::class),
            $urlGenerator = $this->createMock(UrlGeneratorInterface::class),
        );

        $security->expects(static::once())
            ->method('getUser')
            ->willReturn($user)
        ;

        $urlGenerator->expects(static::never())
            ->method('generate')
        ;

        $previousContent = $response->getContent();

        $object->onKernelResponseAddDialog(
            new ResponseEvent(
                static::createStub(HttpKernelInterface::class),
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

    public static function provideOnKernelResponseAddDialogNoInjectionCases(): iterable
    {
        $response = new Response();
        $response->headers->set('Content-Type', 'text/html');
        $response->setContent('<meta data-sonata-admin/><title>value</title>');

        $user = new class implements UserInterface {
            public function getRoles(): array
            {
                return [];
            }

            public function getPassword(): null
            {
                return null;
            }

            public function getSalt(): null
            {
                return null;
            }

            public function eraseCredentials(): void
            {
            }

            public function getUsername(): null
            {
                return null;
            }

            public function getUserIdentifier(): string
            {
                return '';
            }
        };

        yield 'no-user' => [
            $response,
        ];

        yield 'sub-request' => [
            $response,
            $user,
            HttpKernelInterface::SUB_REQUEST,
        ];

        $newResponse = (clone $response);
        $newResponse->headers = new ResponseHeaderBag();
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
}
