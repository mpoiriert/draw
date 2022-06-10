<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\SecurityIntegration;
use Draw\Component\Security\Core\Authentication\SystemAuthenticator;
use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Draw\Component\Security\Core\Listener\SystemConsoleAuthenticatorListener;
use Draw\Component\Security\Http\EventListener\RoleRestrictedAuthenticatorListener;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\SecurityIntegration
 *
 * @property SecurityIntegration $integration
 */
class SecurityIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new SecurityIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'security';
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'system_authentication' => [
                'enabled' => false,
                'roles' => ['ROLE_SYSTEM'],
            ],
            'console_authentication' => [
                'enabled' => false,
                'system_auto_login' => false,
            ],
        ];
    }

    public function provideTestLoad(): iterable
    {
        $defaultServices = [
            new ServiceConfiguration(
                'draw.security.http.event_listener.role_restricted_authenticator_listener',
                [
                    RoleRestrictedAuthenticatorListener::class,
                ]
            ),
        ];

        yield 'default' => [
            [],
            $defaultServices,
        ];

        yield 'system_authentication' => [
            [
                [
                    'system_authentication' => [
                        'roles' => ['ROLE_SYSTEM'],
                    ],
                ],
            ],
            array_merge(
                $defaultServices,
                [
                    new ServiceConfiguration(
                        'draw.security.core.authentication.system_authenticator',
                        [
                            SystemAuthenticator::class,
                        ],
                        function (Definition $definition) {
                            static::assertSame(
                                ['ROLE_SYSTEM'],
                                $definition->getArgument('$roles')
                            );
                        }
                    ),
                ],
            ),
            [
                SystemAuthenticator::class => [
                    SystemAuthenticatorInterface::class,
                ],
            ],
        ];

        yield 'console_authentication' => [
            [
                [
                    'console_authentication' => [
                        'system_auto_login' => true,
                    ],
                ],
            ],
            array_merge(
                $defaultServices,
                [
                    new ServiceConfiguration(
                        'draw.security.core.listener.system_console_authenticator_listener',
                        [
                            SystemConsoleAuthenticatorListener::class,
                        ],
                        function (Definition $definition) {
                            static::assertSame(
                                true,
                                $definition->getArgument('$systemAutoLogin')
                            );
                        }
                    ),
                ],
            ),
        ];
    }
}
