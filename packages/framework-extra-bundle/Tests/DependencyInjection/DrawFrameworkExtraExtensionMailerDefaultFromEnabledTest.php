<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Mailer\EmailWriter\DefaultFromEmailWriter;
use Symfony\Component\DependencyInjection\Definition;

class DrawFrameworkExtraExtensionMailerDefaultFromEnabledTest extends DrawFrameworkExtraExtensionMailerTest
{
    public function getConfiguration(): array
    {
        $configuration = parent::getConfiguration();

        $configuration['mailer']['default_from'] = [
            'email' => 'test@example.com',
            'name' => 'Test Email',
        ];

        return $configuration;
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield from parent::provideTestHasServiceDefinition();
        yield ['draw.mailer.default_from_email_writer'];
        yield [DefaultFromEmailWriter::class, 'draw.mailer.default_from_email_writer'];
    }

    public function testDefaultFromEmailWriterDefinition(): void
    {
        $definition = $this->getContainerBuilder()
            ->getDefinition('draw.mailer.default_from_email_writer');

        $defaultFromDefinition = $definition->getArgument('$defaultFrom');

        $this->assertInstanceOf(
            Definition::class,
            $defaultFromDefinition
        );

        $this->assertSame(
            [
                'test@example.com',
                'Test Email',
            ],
            $defaultFromDefinition->getArguments()
        );
    }
}
