<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Integration;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\IntegrationInterface;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\MailerIntegration;
use Draw\Component\Mailer\Command\SendTestEmailCommand;
use Draw\Component\Mailer\EmailWriter\DefaultFromEmailWriter;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Mailer\EventListener\EmailCssInlinerListener;
use Draw\Component\Mailer\EventListener\EmailSubjectFromHtmlTitleListener;
use Draw\Component\Mailer\EventListener\EmailWriterListener;
use Draw\Component\Mailer\Twig\TranslationExtension;
use ReflectionClass;
use Symfony\Bundle\FrameworkBundle\DependencyInjection\FrameworkExtension;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration\MailerIntegration
 *
 * @property MailerIntegration $integration
 */
class MailerIntegrationTest extends IntegrationTestCase
{
    public function createIntegration(): IntegrationInterface
    {
        return new MailerIntegration();
    }

    public function getConfigurationSectionName(): string
    {
        return 'mailer';
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'css_inliner' => [
                'enabled' => false,
            ],
            'default_from' => [
                'enabled' => false,
            ],
            'subject_from_html_title' => [
                'enabled' => false,
            ],
        ];
    }

    public function testPrependNoFrameworkExtension(): void
    {
        static::expectExceptionMessage(
            'You must have the extension [framework] available to configuration [draw_framework_extra.mailer]'
        );

        $this->integration->prepend(
            new ContainerBuilder(),
            []
        );
    }

    public function testPrepend(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->registerExtension(new FrameworkExtension());
        $containerBuilder->registerExtension(new TwigExtension());

        $this->integration->prepend(
            $containerBuilder,
            []
        );

        $installationPath = dirname(
            (new ReflectionClass(EmailWriterInterface::class))->getFileName(),
            2
        );

        static::assertContainerExtensionConfiguration(
            $containerBuilder,
            [
                'framework' => [
                    [
                        'translator' => [
                            'paths' => [
                                'draw-mailer' => $installationPath.'/Resources/translations',
                            ],
                        ],
                    ],
                ],
                'twig' => [
                    [
                        'paths' => [
                            $installationPath.'/Resources/views' => 'draw-mailer',
                        ],
                    ],
                ],
            ]
        );
    }

    public function provideTestLoad(): iterable
    {
        $defaultServices = [
            new ServiceConfiguration(
                'draw.mailer.twig.translation_extension',
                [
                    TranslationExtension::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.mailer.event_listener.email_writer_listener',
                [
                    EmailWriterListener::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.mailer.command.send_test_email_command',
                [
                    SendTestEmailCommand::class,
                ]
            ),
        ];

        yield 'default' => [
            [],
            $defaultServices,
        ];

        yield 'subject_from_html_title' => [
            [
                ['subject_from_html_title' => true],
            ],
            array_merge(
                $defaultServices,
                [
                    new ServiceConfiguration(
                        'draw.mailer.event_listener.email_subject_from_html_title_listener',
                        [
                            EmailSubjectFromHtmlTitleListener::class,
                        ]
                    ),
                ]
            ),
        ];

        yield 'default_from' => [
            [
                [
                    'default_from' => [
                        'email' => 'test@example.com',
                        'name' => 'Test Email',
                    ],
                ],
            ],
            array_merge(
                $defaultServices,
                [
                    new ServiceConfiguration(
                        'draw.mailer.email_writer.default_from_email_writer',
                        [
                            DefaultFromEmailWriter::class,
                        ],
                        function (Definition $definition) {
                            $defaultFromDefinition = $definition->getArgument('$defaultFrom');

                            static::assertInstanceOf(
                                Definition::class,
                                $defaultFromDefinition
                            );

                            static::assertSame(
                                [
                                    'test@example.com',
                                    'Test Email',
                                ],
                                $defaultFromDefinition->getArguments()
                            );
                        }
                    ),
                ]
            ),
        ];

        yield 'css_inliner' => [
            [
                [
                    'css_inliner' => true,
                ],
            ],
            array_merge(
                $defaultServices,
                [
                    new ServiceConfiguration(
                        'draw.mailer.event_listener.email_css_inliner_listener',
                        [
                            EmailCssInlinerListener::class,
                        ],
                    ),
                ]
            ),
        ];
    }
}
