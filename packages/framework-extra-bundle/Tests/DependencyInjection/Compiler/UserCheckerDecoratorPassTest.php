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

        $definition = $container->findDefinition('draw.security.core.user.event_driven_user_checker');

        static::assertSame(
            EventDrivenUserChecker::class,
            $definition->getClass()
        );

        static::assertSame(
            [
                'security.user_checker',
                'draw.security.core.user.event_driven_user_checker.inner',
                0,
            ],
            $definition->getDecoratedService()
        );

        $argument = $definition->getArgument('$decoratedUserChecker');

        static::assertInstanceOf(Reference::class, $argument);
        static::assertSame('draw.security.core.user.event_driven_user_checker.inner', (string) $argument);
    }
}
