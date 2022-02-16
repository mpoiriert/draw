<?php

namespace Draw\Bundle\PostOfficeBundle\Tests\DependencyInjection;

use Draw\Bundle\PostOfficeBundle\DependencyInjection\DrawPostOfficeExtension;
use Draw\Bundle\PostOfficeBundle\EmailWriter\DefaultFromEmailWriter;
use Draw\Bundle\PostOfficeBundle\EmailWriter\EmailWriterInterface;
use Draw\Bundle\PostOfficeBundle\Listener\EmailEventListener;
use Draw\Bundle\PostOfficeBundle\Twig\TranslationExtension;
use Draw\Component\Tester\DependencyInjection\ExtensionTestCase;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\Mime\Address;

class DrawPostOfficeExtensionTest extends ExtensionTestCase
{
    public function createExtension(): Extension
    {
        return new DrawPostOfficeExtension();
    }

    public function getConfiguration(): array
    {
        return [];
    }

    public function provideTestHasServiceDefinition(): iterable
    {
        yield [EmailEventListener::class];
        yield [TranslationExtension::class];
    }

    public function testEmailWriterInterfaceIsAutoConfigured()
    {
        $container = $this->load([]);
        $childDefinition = $container->getAutoconfiguredInstanceof()[EmailWriterInterface::class];
        $this->assertTrue($childDefinition->hasTag(EmailWriterInterface::class));
    }

    public function testDefaultFromEmail()
    {
        $email = 'support@example.com';
        $container = $this->load([
            'default_from' => ['email' => $email],
        ]);

        $definition = $container->getDefinition('draw_post_office.default_from');
        $this->assertSame(Address::class, $definition->getClass());
        $this->assertSame(
            [$email, ''],
            $definition->getArguments()
        );

        $this->assertTrue($container->has(DefaultFromEmailWriter::class));
    }

    public function testDefaultFromName()
    {
        $email = 'support@example.com';
        $name = 'Acme';
        $container = $this->load([
            'default_from' => ['email' => $email, 'name' => $name],
        ]);

        $definition = $container->getDefinition('draw_post_office.default_from');
        $this->assertSame(Address::class, $definition->getClass());
        $this->assertSame(
            [$email, $name],
            $definition->getArguments()
        );

        $this->assertTrue($container->has(DefaultFromEmailWriter::class));
    }
}
