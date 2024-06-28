<?php

namespace Draw\Component\Security\Tests\DependencyInjection;

use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use Draw\Component\Security\Core\Authentication\SystemAuthenticator;
use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Draw\Component\Security\Core\Authorization\Voter\AbstainRoleHierarchyVoter;
use Draw\Component\Security\Core\EventListener\SystemConsoleAuthenticatorListener;
use Draw\Component\Security\Core\EventListener\SystemMessengerAuthenticatorListener;
use Draw\Component\Security\Core\Security;
use Draw\Component\Security\DependencyInjection\SecurityIntegration;
use Draw\Component\Security\Http\EventListener\RoleRestrictedAuthenticatorListener;
use Draw\Component\Security\Jwt\JwtEncoder;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @property SecurityIntegration $integration
 */
#[CoversClass(SecurityIntegration::class)]
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
            'console_authentication' => [
                'enabled' => false,
                'system_auto_login' => false,
            ],
            'messenger_authentication' => [
                'enabled' => false,
                'system_auto_login' => true,
            ],
            'jwt' => [
                'encoder' => [
                    'enabled' => false,
                    'algorithm' => 'HS256',
                ],
            ],
            'system_authentication' => [
                'enabled' => false,
                'roles' => ['ROLE_SYSTEM'],
            ],
            'voters' => [
                'abstain_role_hierarchy' => [
                    'enabled' => false,
                ],
            ],
        ];
    }

    public static function provideTestLoad(): iterable
    {
        $defaultServices = [
            new ServiceConfiguration(
                'draw.security.http.event_listener.role_restricted_authenticator_listener',
                [
                    RoleRestrictedAuthenticatorListener::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.security.core.security',
                [
                    Security::class,
                ]
            ),
        ];

        yield 'default' => [
            [],
            $defaultServices,
        ];

        yield 'console_authentication' => [
            [
                [
                    'console_authentication' => [
                        'system_auto_login' => true,
                    ],
                ],
            ],
            [
                ...$defaultServices,
                ...[
                    new ServiceConfiguration(
                        'draw.security.core.event_listener.system_console_authenticator_listener',
                        [
                            SystemConsoleAuthenticatorListener::class,
                        ],
                        function (Definition $definition): void {
                            static::assertTrue(
                                $definition->getArgument('$systemAutoLogin')
                            );
                        }
                    ),
                ],
            ],
        ];

        yield 'jwt' => [
            [
                [
                    'jwt' => [
                        'encoder' => [
                            'key' => $key = uniqid('key-'),
                        ],
                    ],
                ],
            ],
            [
                ...$defaultServices,
                ...[
                    new ServiceConfiguration(
                        'draw.security.jwt.jwt_encoder',
                        [
                            JwtEncoder::class,
                        ],
                        function (Definition $definition) use ($key): void {
                            static::assertSame(
                                $key,
                                $definition->getArgument('$key')
                            );

                            static::assertSame(
                                'HS256',
                                $definition->getArgument('$algorithm')
                            );
                        }
                    ),
                ],
            ],
        ];

        yield 'messenger_authentication' => [
            [
                [
                    'messenger_authentication' => [
                        'system_auto_login' => true,
                    ],
                ],
            ],
            [
                ...$defaultServices,
                ...[
                    new ServiceConfiguration(
                        'draw.security.core.event_listener.system_messenger_authenticator_listener',
                        [
                            SystemMessengerAuthenticatorListener::class,
                        ],
                    ),
                ],
            ],
        ];

        yield 'system_authentication' => [
            [
                [
                    'system_authentication' => [
                        'roles' => ['ROLE_SYSTEM'],
                    ],
                ],
            ],
            [
                ...$defaultServices,
                ...[
                    new ServiceConfiguration(
                        'draw.security.core.authentication.system_authenticator',
                        [
                            SystemAuthenticator::class,
                        ],
                        function (Definition $definition): void {
                            static::assertSame(
                                ['ROLE_SYSTEM'],
                                $definition->getArgument('$roles')
                            );
                        }
                    ),
                ],
            ],
            [
                SystemAuthenticator::class => [
                    SystemAuthenticatorInterface::class,
                ],
            ],
        ];

        yield 'voters' => [
            [
                [
                    'voters' => [
                        'abstain_role_hierarchy' => true,
                    ],
                ],
            ],
            [
                ...$defaultServices,
                ...[
                    new ServiceConfiguration(
                        'draw.security.voter.abstain_role_hierarchy_voter',
                        [
                            AbstainRoleHierarchyVoter::class,
                        ],
                    ),
                ],
            ],
            [
                AbstainRoleHierarchyVoter::class => [
                    'security.access.role_hierarchy_voter',
                ],
            ],
        ];
    }
}
