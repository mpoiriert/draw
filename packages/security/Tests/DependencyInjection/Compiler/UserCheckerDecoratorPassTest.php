<?php

namespace DependencyInjection\Compiler;

use Draw\Component\Security\Core\User\EventDrivenUserChecker;
use Draw\Component\Security\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
#[CoversClass(UserCheckerDecoratorPass::class)]
class UserCheckerDecoratorPassTest extends TestCase
{
    private UserCheckerDecoratorPass $compilerPass;

    protected function setUp(): void
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

        $this->assertSame(
            EventDrivenUserChecker::class,
            $definition->getClass()
        );

        $this->assertSame(
            [
                'security.user_checker',
                'draw.security.core.user.event_driven_user_checker.inner',
                0,
            ],
            $definition->getDecoratedService()
        );

        $argument = $definition->getArgument('$decoratedUserChecker');

        $this->assertInstanceOf(Reference::class, $argument);
        $this->assertSame('draw.security.core.user.event_driven_user_checker.inner', (string) $argument);
    }
}
