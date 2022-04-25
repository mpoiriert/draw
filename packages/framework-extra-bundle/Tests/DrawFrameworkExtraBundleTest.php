<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddCommandExecutionOptionsCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddNewestInstanceRoleCommandOptionPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerBrokerCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerTransportNamesCompilerPass;
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

    public function setUp(): void
    {
        $this->bundle = new DrawFrameworkExtraBundle();
    }

    public function testBuild(): void
    {
        $containerBuilder = $this->createMock(ContainerBuilder::class);

        $containerBuilder
            ->expects($this->exactly(5))
            ->method('addCompilerPass')
            ->withConsecutive(
                [
                    $this->isInstanceOf(UserCheckerDecoratorPass::class),
                ],
                [
                    $this->isInstanceOf(MessengerBrokerCompilerPass::class),
                ],
                [
                    $this->isInstanceOf(AddNewestInstanceRoleCommandOptionPass::class),
                ],
                [
                    $this->isInstanceOf(AddCommandExecutionOptionsCompilerPass::class),
                ],
                [
                    $this->isInstanceOf(MessengerTransportNamesCompilerPass::class),
                    PassConfig::TYPE_BEFORE_OPTIMIZATION,
                    -1,
                ],
            )
            ->willReturnSelf();

        $containerBuilder
            ->expects($this->once())
            ->method('hasExtension')
            ->with('security')
            ->willReturn(true);

        $containerBuilder
            ->expects($this->once())
            ->method('getExtension')
            ->with('security')
            ->willReturn($extension = $this->createMock(SecurityExtension::class));

        $extension
            ->expects($this->exactly(2))
            ->method('addAuthenticatorFactory')
            ->withConsecutive(
                [$this->isInstanceOf(JwtAuthenticatorFactory::class)],
                [$this->isInstanceOf(MessengerMessageAuthenticatorFactory::class)],
            );

        $this->bundle->build($containerBuilder);
    }
}
