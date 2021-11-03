<?php

namespace Draw\Bundle\SonataExtraBundle\Tests\DependencyInjection\Compiler;

use Draw\Bundle\SonataExtraBundle\DependencyInjection\Compiler\ExtractArgumentFromDefaultValueCompilerPass;
use Draw\Bundle\SonataExtraBundle\Tests\Mock\Admin;
use Draw\Bundle\SonataExtraBundle\Tests\Mock\Entity;
use PHPUnit\Framework\TestCase;
use Sonata\AdminBundle\DependencyInjection\Admin\TaggedAdminInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ExtractArgumentFromDefaultValueCompilerPassTest extends TestCase
{
    private $compilerPass;

    private $containerBuilder;

    public function setUp(): void
    {
        $this->compilerPass = new ExtractArgumentFromDefaultValueCompilerPass();
        $this->containerBuilder = new ContainerBuilder();
    }

    public function testProcess(): void
    {
        $this->containerBuilder->setParameter('admin.class', Admin::class);

        $definition = $this->containerBuilder->setDefinition(
            Admin::class,
            new Definition('%admin.class%')
        )->addTag(TaggedAdminInterface::ADMIN_TAG);

        $this->compilerPass->process($this->containerBuilder);

        $this->assertSame(
            null,
            $definition->getArgument(0)
        );

        $this->assertSame(
            Entity::class,
            $definition->getArgument(1)
        );

        $this->assertSame(
            null,
            $definition->getArgument(2)
        );
    }

    public function testProcessAlreadySet(): void
    {
        $definition = $this->containerBuilder->setDefinition(
            Admin::class,
            new Definition(Admin::class)
        )
            ->addTag(TaggedAdminInterface::ADMIN_TAG)
            ->setArgument(1, $argument = 'toto');

        $this->compilerPass->process($this->containerBuilder);

        $this->assertSame(
            null,
            $definition->getArgument(0)
        );

        $this->assertSame(
            $argument,
            $definition->getArgument(1)
        );

        $this->assertSame(
            null,
            $definition->getArgument(2)
        );
    }
}
