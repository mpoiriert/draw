<?php

namespace Draw\Component\Mailer\DependencyInjection;

use Draw\Component\DependencyInjection\Integration\ContainerBuilderIntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Draw\Component\DependencyInjection\Integration\PrependIntegrationInterface;
use Draw\Component\Mailer\BodyRenderer\LocalizeBodyRenderer;
use Draw\Component\Mailer\DependencyInjection\Compiler\EmailWriterCompilerPass;
use Draw\Component\Mailer\EmailComposer;
use Draw\Component\Mailer\EmailWriter\DefaultFromEmailWriter;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Mailer\EventListener\EmailCssInlinerListener;
use Draw\Component\Mailer\EventListener\EmailSubjectFromHtmlTitleListener;
use Pelago\Emogrifier\CssInliner;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Mime\Address;

class MailerIntegration implements IntegrationInterface, ContainerBuilderIntegrationInterface, PrependIntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'mailer';
    }

    public function buildContainer(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new EmailWriterCompilerPass());
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\Mailer\\',
            \dirname(
                (new \ReflectionClass(EmailComposer::class))->getFileName(),
            ),
        );

        $container
            ->getDefinition(LocalizeBodyRenderer::class)
            ->setDecoratedService(
                'twig.mime_body_renderer',
                'draw.mailer.body_renderer.localize_body_renderer.inner'
            )
            ->setArgument('$bodyRenderer', new Reference('draw.mailer.body_renderer.localize_body_renderer.inner'));

        $container
            ->registerForAutoconfiguration(EmailWriterInterface::class)
            ->addTag(EmailWriterInterface::class);

        if (!$this->isConfigEnabled($container, $config['subject_from_html_title'])) {
            $container->removeDefinition(EmailSubjectFromHtmlTitleListener::class);
        }

        if (!$this->isConfigEnabled($container, $config['css_inliner'])) {
            $container->removeDefinition(EmailCssInlinerListener::class);
        }

        if (!$this->isConfigEnabled($container, $config['default_from'])) {
            $container->removeDefinition(DefaultFromEmailWriter::class);
        } else {
            $container
                ->getDefinition(DefaultFromEmailWriter::class)
                ->setArgument(
                    '$defaultFrom',
                    (new Definition(Address::class))
                        ->setArguments([$config['default_from']['email'], $config['default_from']['name'] ?? ''])
                );
        }

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.mailer.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
              ->children()
                ->arrayNode('subject_from_html_title')
                    ->canBeEnabled()
                ->end()
                ->arrayNode('css_inliner')
                    ->canBeEnabled()
                    ->validate()
                        ->ifTrue(fn ($value) => $value['enabled'] && !class_exists(CssInliner::class))
                        ->thenInvalid('The css inliner is base on the [pelago/emogrifier] package. Install it if you want to enable this feature.')
                    ->end()
                ->end()
                ->arrayNode('default_from')
                    ->canBeEnabled()
                    ->children()
                        ->scalarNode('email')->isRequired()->end()
                        ->scalarNode('name')->end()
                    ->end()
                ->end()
            ->end();
    }

    public function prepend(ContainerBuilder $container, array $config): void
    {
        $installationPath = \dirname((new \ReflectionClass(EmailWriterInterface::class))->getFileName(), 2);

        $this->assertHasExtension($container, 'framework');

        $container->prependExtensionConfig(
            'framework',
            [
                'translator' => [
                    'paths' => [
                        'draw-mailer' => $installationPath.'/Resources/translations',
                    ],
                ],
            ]
        );

        if ($container->hasExtension('twig')) {
            $container->prependExtensionConfig(
                'twig',
                [
                    'paths' => [
                        $installationPath.'/Resources/views' => 'draw-mailer',
                    ],
                ]
            );
        }
    }
}
