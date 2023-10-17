<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\User\Action;

use Draw\Bundle\SonataIntegrationBundle\User\Action\TwoFactorAuthenticationResendCodeAction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Scheb\TwoFactorBundle\Model\Email\TwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Email\Generator\CodeGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(TwoFactorAuthenticationResendCodeAction::class)]
class TwoFactorAuthenticationResendCodeActionTest extends TestCase
{
    private TwoFactorAuthenticationResendCodeAction $object;

    private CodeGeneratorInterface&MockObject $codeGenerator;

    private UrlGeneratorInterface&MockObject $urlGenerator;

    protected function setUp(): void
    {
        $this->object = new TwoFactorAuthenticationResendCodeAction(
            $this->codeGenerator = $this->createMock(CodeGeneratorInterface::class),
            $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class)
        );
    }

    public function testInvoke(): void
    {
        $user = new class() implements UserInterface, TwoFactorInterface {
            public function isEmailAuthEnabled(): bool
            {
                return true;
            }

            public function getEmailAuthRecipient(): string
            {
                return '';
            }

            public function getEmailAuthCode(): string
            {
                return '';
            }

            public function setEmailAuthCode(string $authCode): void
            {
            }

            public function getRoles(): array
            {
                return [];
            }

            public function getPassword(): ?string
            {
                return null;
            }

            public function getSalt(): ?string
            {
                return null;
            }

            public function eraseCredentials(): void
            {
            }

            public function getUsername(): string
            {
                return '';
            }
        };

        $this->codeGenerator
            ->expects(static::once())
            ->method('generateAndSend')
            ->with($user);

        $this->urlGenerator
            ->expects(static::once())
            ->method('generate')
            ->with('admin_2fa_login', ['preferProvider' => 'email'])
            ->willReturn($url = uniqid('https://'));

        $securityMock = $this->createMock(Security::class);
        $securityMock
            ->expects(static::once())
            ->method('getUser')
            ->willReturn($user);

        $result = \call_user_func(
            $this->object,
            $securityMock
        );

        static::assertInstanceOf(
            RedirectResponse::class,
            $result
        );

        static::assertSame(
            $url,
            $result->getTargetUrl()
        );
    }
}
