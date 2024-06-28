<?php

namespace Draw\Component\Mailer\Tests\DependencyInjection;

use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\Test\IntegrationTestCase;
use Draw\Component\DependencyInjection\Integration\Test\ServiceConfiguration;
use Draw\Component\Mailer\BodyRenderer\LocalizeBodyRenderer;
use Draw\Component\Mailer\Command\SendTestEmailCommand;
use Draw\Component\Mailer\DependencyInjection\MailerIntegration;
use Draw\Component\Mailer\EmailComposer;
use Draw\Component\Mailer\EmailWriter\AddTemplateHeaderEmailWriter;
use Draw\Component\Mailer\EmailWriter\DefaultFromEmailWriter;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Mailer\EventListener\EmailComposerListener;
use Draw\Component\Mailer\EventListener\EmailCssInlinerListener;
use Draw\Component\Mailer\EventListener\EmailSubjectFromHtmlTitleListener;
use Draw\Component\Mailer\Twig\TranslationExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * @property MailerIntegration $integration
 */
#[CoversClass(MailerIntegration::class)]
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
        $containerBuilder->registerExtension($this->mockExtension('framework'));
        $containerBuilder->registerExtension($this->mockExtension('twig'));

        $this->integration->prepend(
            $containerBuilder,
            []
        );

        $installationPath = \dirname(
            (new \ReflectionClass(EmailWriterInterface::class))->getFileName(),
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

    public static function provideTestLoad(): iterable
    {
        $defaultServices = [
            new ServiceConfiguration(
                'draw.mailer.twig.translation_extension',
                [
                    TranslationExtension::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.mailer.email_composer',
                [
                    EmailComposer::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.mailer.body_renderer.localize_body_renderer',
                [
                    LocalizeBodyRenderer::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.mailer.email_writer.add_template_header_email_writer',
                [
                    AddTemplateHeaderEmailWriter::class,
                ]
            ),
            new ServiceConfiguration(
                'draw.mailer.event_listener.email_composer_listener',
                [
                    EmailComposerListener::class,
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
                        function (Definition $definition): void {
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
