<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddCommandExecutionOptionsCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddNewestInstanceRoleCommandOptionPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\EmailWriterCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\JmsDoctrineObjectConstructionCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerBrokerCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerTransportNamesCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\TagIfExpressionCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security\JwtAuthenticatorFactory;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security\MessengerMessageAuthenticatorFactory;
use Draw\Bundle\FrameworkExtraBundle\DrawFrameworkExtraBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class DrawFrameworkExtraBundleTest extends TestCase
{
    private DrawFrameworkExtraBundle $bundle;

    protected function setUp(): void
    {
        $this->bundle = new DrawFrameworkExtraBundle();
    }

    public function testBuild(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder
            ->expects(static::exactly(8))
            ->method('addCompilerPass')
            ->withConsecutive(
                [
                    static::isInstanceOf(TagIfExpressionCompilerPass::class),
                ],
                [
                    static::isInstanceOf(UserCheckerDecoratorPass::class),
                ],
                [
                    static::isInstanceOf(MessengerBrokerCompilerPass::class),
                ],
                [
                    static::isInstanceOf(AddNewestInstanceRoleCommandOptionPass::class),
                ],
                [
                    static::isInstanceOf(AddCommandExecutionOptionsCompilerPass::class),
                ],
                [
                    static::isInstanceOf(EmailWriterCompilerPass::class),
                ],
                [
                    static::isInstanceOf(MessengerTransportNamesCompilerPass::class),
                    PassConfig::TYPE_BEFORE_OPTIMIZATION,
                    -1,
                ],
                [
                    static::isInstanceOf(JmsDoctrineObjectConstructionCompilerPass::class),
                ],
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
            ->withConsecutive(
                [static::isInstanceOf(JwtAuthenticatorFactory::class)],
                [static::isInstanceOf(MessengerMessageAuthenticatorFactory::class)],
            );

        $this->bundle->build($containerBuilder);
    }
}
