<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection\Compiler;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Component\Security\Core\User\EventDrivenUserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass
 */
class UserCheckerDecoratorPassTest extends TestCase
{
    private UserCheckerDecoratorPass $compilerPass;

    public function setUp(): void
    {
        $this->compilerPass = new UserCheckerDecoratorPass();
    }

    public function testProcess(): void
    {
        $container = new ContainerBuilder();
        $container->setDefinition(
            'security.user_checker',
            new Definition()
        );

        $this->compilerPass->process($container);

        $definition = $container->getDefinition('draw.security.event_driver_user_checker');

        $this->assertSame(
            EventDrivenUserChecker::class,
            $definition->getClass()
        );

        $this->assertSame(
            [
                'security.user_checker',
                'draw.security.event_driver_user_checker.inner',
                0,
            ],
            $definition->getDecoratedService()
        );

        $argument = $definition->getArgument('$decoratedUserChecker');

        $this->assertInstanceOf(Reference::class, $argument);
        $this->assertSame('draw.security.event_driver_user_checker.inner', (string) $argument);
    }
}
