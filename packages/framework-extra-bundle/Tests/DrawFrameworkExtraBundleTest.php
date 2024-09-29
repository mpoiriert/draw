<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests;

use Draw\Bundle\FrameworkExtraBundle\DrawFrameworkExtraBundle;
use Draw\Component\AwsToolKit\DependencyInjection\Compiler\AddNewestInstanceRoleCommandOptionPass;
use Draw\Component\Console\DependencyInjection\Compiler\AddCommandExecutionOptionsCompilerPass;
use Draw\Component\CronJob\DependencyInjection\Compiler\AddPostCronJobExecutionOptionPass;
use Draw\Component\DependencyInjection\DependencyInjection\Compiler\TagIfExpressionCompilerPass;
use Draw\Component\EntityMigrator\DependencyInjection\Compiler\EntityMigratorCompilerPass;
use Draw\Component\Log\DependencyInjection\Compiler\LoggerDecoratorPass;
use Draw\Component\Mailer\DependencyInjection\Compiler\EmailWriterCompilerPass;
use Draw\Component\Messenger\DependencyInjection\Compiler\MessengerTransportNamesCompilerPass;
use Draw\Component\OpenApi\DependencyInjection\Compiler\JmsDoctrineObjectConstructionCompilerPass;
use Draw\Component\Security\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Component\Security\DependencyInjection\Factory\JwtAuthenticatorFactory;
use Draw\Component\Security\DependencyInjection\Factory\MessengerMessageAuthenticatorFactory;
use Draw\Component\Tester\MockTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
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
            ->expects(static::exactly(10))
            ->method('addCompilerPass')
            ->with(
                ...static::withConsecutive(
                    [
                        static::isInstanceOf(TagIfExpressionCompilerPass::class),
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
                        static::isInstanceOf(AddPostCronJobExecutionOptionPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        0,
                    ],
                    [
                        static::isInstanceOf(EntityMigratorCompilerPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        0,
                    ],
                    [
                        static::isInstanceOf(LoggerDecoratorPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        -1,
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
                    [
                        static::isInstanceOf(UserCheckerDecoratorPass::class),
                        PassConfig::TYPE_BEFORE_OPTIMIZATION,
                        0,
                    ],
                )
            )
            ->willReturnSelf()
        ;

        $containerBuilder
            ->expects(static::once())
            ->method('hasExtension')
            ->with('security')
            ->willReturn(true)
        ;

        $containerBuilder
            ->expects(static::once())
            ->method('getExtension')
            ->with('security')
            ->willReturn($extension = $this->createMock(SecurityExtension::class))
        ;

        $extension
            ->expects(static::exactly(2))
            ->method('addAuthenticatorFactory')
            ->with(
                ...static::withConsecutive(
                    [static::isInstanceOf(JwtAuthenticatorFactory::class)],
                    [static::isInstanceOf(MessengerMessageAuthenticatorFactory::class)],
                )
            )
        ;

        $this->bundle->build($containerBuilder);
    }
}
