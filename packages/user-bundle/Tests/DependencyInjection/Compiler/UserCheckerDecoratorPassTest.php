<?php

namespace Draw\Bundle\UserBundle\Tests\DependencyInjection\Compiler;

use Draw\Bundle\UserBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Bundle\UserBundle\Security\User\EventDrivenUserChecker;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @covers \Draw\Bundle\UserBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass
 */
class UserCheckerDecoratorPassTest extends TestCase
{
    private $compilerPass;

    public function setUp(): void
    {
        $this->compilerPass = new UserCheckerDecoratorPass();
    }

    public function testProcess(): void
    {
        $containerBuilder = new ContainerBuilder();
        $containerBuilder->setDefinition(
            'security.user_checker',
            new Definition()
        );

        $this->compilerPass->process($containerBuilder);

        $definition = $containerBuilder->getDefinition(EventDrivenUserChecker::class);

        $this->assertSame(
            [
                'security.user_checker',
                'security.user_checker.inner',
                0,
            ],
            $definition->getDecoratedService()
        );

        $argument = $definition->getArgument('$decoratedUserChecker');

        $this->assertInstanceOf(Reference::class, $argument);
        $this->assertSame('security.user_checker.inner', (string) $argument);
    }
}
