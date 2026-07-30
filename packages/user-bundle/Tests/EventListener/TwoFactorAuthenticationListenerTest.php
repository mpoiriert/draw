<?php

namespace Draw\Bundle\UserBundle\Tests\EventListener;

use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Entity\SecurityUserTrait;
use Draw\Bundle\UserBundle\Event\UserRequestInterceptionEvent;
use Draw\Bundle\UserBundle\EventListener\TwoFactorAuthenticationListener;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ByTimeBaseOneTimePasswordInterface;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ByTimeBaseOneTimePasswordTrait;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\ConfigurationTrait;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Entity\TwoFactorAuthenticationUserInterface;
use Draw\Component\Security\Core\Security;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
class TwoFactorAuthenticationListenerTest extends TestCase
{
    private const string ENABLE_ROUTE = 'route';

    private TwoFactorAuthenticationListener $object;

    private UrlGeneratorInterface&MockObject $urlGenerator;

    private Security&MockObject $security;

    protected function setUp(): void
    {
        $this->object = new TwoFactorAuthenticationListener(
            $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class),
            $this->security = $this->createMock(Security::class),
            self::ENABLE_ROUTE
        );
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(
            EventSubscriberInterface::class,
            $this->object
        );
    }

    public function testGetSubscribedEvents(): void
    {
        $this->assertSame(
            [
                UserRequestInterceptionEvent::class => [
                    ['checkNeedToEnableTwoFactorAuthentication', 50],
                    ['allowHandlingRequestWhenTwoFactorAuthenticationInProgress', 1000],
                ],
            ],
            $this->object::getSubscribedEvents()
        );
    }

    #[DataProvider('provideCheckNeedToEnableTwoFactorAuthenticationCases')]
    public function testCheckNeedToEnableTwoFactorAuthentication(
        UserRequestInterceptionEvent $event,
        bool $allowHandingRequest,
        bool $redirect,
    ): void {
        $url = null;
        if ($redirect) {
            $user = $event->getUser();

            $this->assertInstanceOf(SecurityUserInterface::class, $user);

            $this->urlGenerator
                ->expects($this->once())
                ->method('generate')
                ->with(
                    self::ENABLE_ROUTE,
                    ['id' => $user->getId()]
                )
                ->willReturn($url = uniqid('url'))
            ;
        }

        $this->object->checkNeedToEnableTwoFactorAuthentication($event);

        $this->assertSame($allowHandingRequest, $event->getAllowHandlingRequest());

        $response = $event->getResponse();

        if (!$redirect) {
            $this->assertNull($response);

            return;
        }

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame($url, $response->getTargetUrl());
        $this->assertSame('2fa_need_enabling', $event->getReason());
    }

    public static function provideCheckNeedToEnableTwoFactorAuthenticationCases(): iterable
    {
        $request = new Request();
        $request->attributes->set('_route', self::ENABLE_ROUTE);

        yield 'not-security-user' => [
            new UserRequestInterceptionEvent(
                new class implements UserInterface {
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

                    public function getUserIdentifier(): string
                    {
                        return '';
                    }

                    public function eraseCredentials(): void
                    {
                    }
                },
                $request
            ),
            false,
            false,
        ];

        yield 'not-two-factor-authentication-user' => [
            new UserRequestInterceptionEvent(
                new class implements SecurityUserInterface, TwoFactorAuthenticationUserInterface {
                    use ConfigurationTrait {
                        asOneTwoFActorAuthenticationProviderEnabled as originalAsOneProviderEnabled;
                    }

                    use SecurityUserTrait;

                    public function getId(): mixed
                    {
                        return 1;
                    }

                    public function asOneTwoFActorAuthenticationProviderEnabled(): bool
                    {
                        return false;
                    }
                },
                $request
            ),
            false,
            false,
        ];

        yield 'not-as-one-provider-enable' => [
            new UserRequestInterceptionEvent(
                new class implements SecurityUserInterface, TwoFactorAuthenticationUserInterface {
                    use ConfigurationTrait {
                        asOneTwoFActorAuthenticationProviderEnabled as originalAsOneProviderEnabled;
                    }

                    use SecurityUserTrait;

                    public function getId(): mixed
                    {
                        return 1;
                    }

                    public function asOneTwoFActorAuthenticationProviderEnabled(): bool
                    {
                        return true;
                    }
                },
                $request
            ),
            false,
            false,
        ];

        yield 'not-force-enabling-two-factor-authentication' => [
            new UserRequestInterceptionEvent(
                new class implements SecurityUserInterface, TwoFactorAuthenticationUserInterface {
                    use ConfigurationTrait;
                    use SecurityUserTrait;

                    public function getId(): mixed
                    {
                        return 1;
                    }
                },
                $request
            ),
            false,
            false,
        ];

        yield 'not-by-time-base-one-time-password' => [
            new UserRequestInterceptionEvent(
                new class implements SecurityUserInterface, TwoFactorAuthenticationUserInterface {
                    use ConfigurationTrait {
                        isForceEnablingTwoFactorAuthentication as originalIsForceEnablingTwoFactorAuthentication;
                    }

                    use SecurityUserTrait;

                    public function getId(): mixed
                    {
                        return 1;
                    }

                    public function isForceEnablingTwoFactorAuthentication(): bool
                    {
                        return true;
                    }
                },
                $request
            ),
            false,
            false,
        ];

        yield 'enabled-route' => [
            new UserRequestInterceptionEvent(
                new class implements SecurityUserInterface, TwoFactorAuthenticationUserInterface, ByTimeBaseOneTimePasswordInterface {
                    use ByTimeBaseOneTimePasswordTrait {
                        isForceEnablingTwoFactorAuthentication as originalIsForceEnablingTwoFactorAuthentication;
                    }

                    use SecurityUserTrait;

                    public function getId(): mixed
                    {
                        return 1;
                    }

                    public function isForceEnablingTwoFactorAuthentication(): bool
                    {
                        return true;
                    }
                },
                $request
            ),
            true,
            false,
        ];

        yield 'not-enabled-route' => [
            new UserRequestInterceptionEvent(
                new class implements SecurityUserInterface, TwoFactorAuthenticationUserInterface, ByTimeBaseOneTimePasswordInterface {
                    use ByTimeBaseOneTimePasswordTrait {
                        isForceEnablingTwoFactorAuthentication as originalIsForceEnablingTwoFactorAuthentication;
                    }
                    use SecurityUserTrait;

                    public function getId(): mixed
                    {
                        return 1;
                    }

                    public function isForceEnablingTwoFactorAuthentication(): bool
                    {
                        return true;
                    }
                },
                new Request()
            ),
            false,
            true,
        ];
    }

    public function testAllowHandlingRequestWhenTwoFactorAuthenticationInProgressTrue(): void
    {
        $this->security
            ->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_2FA_IN_PROGRESS')
            ->willReturn(true)
        ;

        $this->object->allowHandlingRequestWhenTwoFactorAuthenticationInProgress(
            $event = new UserRequestInterceptionEvent(
                $this->createStub(SecurityUserInterface::class),
                new Request()
            )
        );

        $this->assertTrue($event->getAllowHandlingRequest());
    }

    public function testAllowHandlingRequestWhenTwoFactorAuthenticationInProgressFalse(): void
    {
        $this->security
            ->expects($this->once())
            ->method('isGranted')
            ->with('IS_AUTHENTICATED_2FA_IN_PROGRESS')
            ->willReturn(false)
        ;

        $this->object->allowHandlingRequestWhenTwoFactorAuthenticationInProgress(
            $event = new UserRequestInterceptionEvent(
                $this->createStub(SecurityUserInterface::class),
                new Request()
            )
        );

        $this->assertFalse($event->getAllowHandlingRequest());
    }
}
