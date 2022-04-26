<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Mailer\EventListener\EmailWriterListener;
use Draw\Component\Mailer\Twig\TranslationExtension;
use ReflectionClass;
use Symfony\Bundle\TwigBundle\DependencyInjection\TwigExtension;

class DrawFrameworkExtraExtensionMailerTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['mailer'] = [
            'enabled' => true,
            'subject_from_html_title' => [
                'enabled' => false,
            ],
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.mailer.twig.translation_extension'];
        yield [TranslationExtension::class, 'draw.mailer.twig.translation_extension'];
        yield ['draw.mailer.email_writer_listener'];
        yield [EmailWriterListener::class, 'draw.mailer.email_writer_listener'];
    }

    public function testPrepend(): void
    {
        $containerBuilder = static::getContainerBuilder();

        $containerBuilder->registerExtension($this->getExtension());
        $containerBuilder->registerExtension(new TwigExtension());

        $containerBuilder->loadFromExtension('draw_framework_extra', $this->getConfiguration());

        $this->getExtension()->prepend($containerBuilder);

        $installationPath = dirname((new ReflectionClass(EmailWriterInterface::class))->getFileName(), 2);

        $this->assertSame(
            [
                [
                    'translator' => [
                        'paths' => [
                            'draw-mailer' => $installationPath.'/Resources/translations',
                        ],
                    ],
                ],
            ],
            $containerBuilder->getExtensionConfig('framework')
        );

        $this->assertSame(
            [
                [
                    'paths' => [
                        $installationPath.'/Resources/views' => 'draw-mailer',
                    ],
                ],
            ],
            $containerBuilder->getExtensionConfig('twig')
        );
    }
}
