<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddCommandExecutionOptionsCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddNewestInstanceRoleCommandOptionPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\EmailWriterCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\JmsDoctrineObjectConstructionCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\LoggerDecoratorPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerBrokerCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerTransportNamesCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\TagIfExpressionCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security\JwtAuthenticatorFactory;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security\MessengerMessageAuthenticatorFactory;
use Draw\Bundle\FrameworkExtraBundle\DrawFrameworkExtraBundle;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DrawFrameworkExtraBundleTest extends TestCase
{
    use MockTrait;

    private DrawFrameworkExtraBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new DrawFrameworkExtraBundle();
    }

    public function testBuild(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder
            ->expects(static::exactly(9))
            ->method('addCompilerPass')
            ->with(
                ...static::withConsecutive(
                    [
                        static::isInstanceOf(TagIfExpressionCompilerPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        0,
                    ],
                    [
                        static::isInstanceOf(LoggerDecoratorPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        -1,
                    ],
                    [
                        static::isInstanceOf(UserCheckerDecoratorPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        0,
                    ],
                    [
                        static::isInstanceOf(MessengerBrokerCompilerPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        0,
                    ],
                    [
                        static::isInstanceOf(AddNewestInstanceRoleCommandOptionPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        0,
                    ],
                    [
                        static::isInstanceOf(AddCommandExecutionOptionsCompilerPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        0,
                    ],
                    [
                        static::isInstanceOf(EmailWriterCompilerPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        0,
                    ],
                    [
                        static::isInstanceOf(MessengerTransportNamesCompilerPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        -1,
                    ],
                    [
                        static::isInstanceOf(JmsDoctrineObjectConstructionCompilerPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        0,
                    ],
                )
            )
            ->willReturnSelf();

        $containerBuilder
            ->expects(static::once())
            ->method('hasExtension')
            ->with('security')
            ->willReturn(true);

        $containerBuilder
            ->expects(static::once())
            ->method('getExtension')
            ->with('security')
            ->willReturn($extension = $this->createMock(SecurityExtension::class));

        $extension
            ->expects(static::exactly(2))
            ->method('addAuthenticatorFactory')
            ->with(
                ...static::withConsecutive(
                    [static::isInstanceOf(JwtAuthenticatorFactory::class)],
                    [static::isInstanceOf(MessengerMessageAuthenticatorFactory::class)],
                )
            );

        $this->bundle->build($containerBuilder);
    }
}
