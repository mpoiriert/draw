<?php

namespace Draw\Bundle\FrameworkExtraBundle;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddCommandExecutionOptionsCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddNewestInstanceRoleCommandOptionPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddPostCronJobExecutionOptionPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\EmailWriterCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\EntityMigratorCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\JmsDoctrineObjectConstructionCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\LoggerDecoratorPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerBrokerCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerRetryFailedMessageMessageHandlerCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerTransportNamesCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\TagIfExpressionCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security\JwtAuthenticatorFactory;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security\MessengerMessageAuthenticatorFactory;
use Draw\Component\Console\EventListener\CommandFlowListener;
use Draw\Component\EntityMigrator\Migrator;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Messenger\Broker\Broker;
use Draw\Component\Messenger\Expirable\Command\PurgeExpiredMessageCommand;
use Draw\Component\Messenger\MessageHandler\RetryFailedMessageMessageHandler;
use Draw\Component\OpenApi\OpenApi;
use Draw\Component\Security\Core\User\EventDrivenUserChecker;
use Draw\Component\Security\Http\Authenticator\JwtAuthenticator;
use Draw\Component\Security\Http\Authenticator\MessageAuthenticator;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawFrameworkExtraBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new TagIfExpressionCompilerPass());

        // It needs to run after the LoggerChannelPass
        $container->addCompilerPass(new LoggerDecoratorPass(), priority: -1);

        if (class_exists(EventDrivenUserChecker::class)) {
            $container->addCompilerPass(new UserCheckerDecoratorPass());
        }

        if (class_exists(Broker::class)) {
            $container->addCompilerPass(new MessengerBrokerCompilerPass());
        }

        if (class_exists(RetryFailedMessageMessageHandler::class)) {
            $container->addCompilerPass(new MessengerRetryFailedMessageMessageHandlerCompilerPass());
        }

        if (class_exists(AddNewestInstanceRoleCommandOptionPass::class)) {
            $container->addCompilerPass(new AddNewestInstanceRoleCommandOptionPass());
        }

        if (class_exists(AddPostCronJobExecutionOptionPass::class)) {
            $container->addCompilerPass(new AddPostCronJobExecutionOptionPass());
        }

        if (class_exists(CommandFlowListener::class)) {
            $container->addCompilerPass(new AddCommandExecutionOptionsCompilerPass());
        }

        if (interface_exists(EmailWriterInterface::class)) {
            $container->addCompilerPass(new EmailWriterCompilerPass());
        }

        if (class_exists(Migrator::class)) {
            $container->addCompilerPass(new EntityMigratorCompilerPass());
        }

        if (class_exists(PurgeExpiredMessageCommand::class)) {
            $container->addCompilerPass(
                new MessengerTransportNamesCompilerPass(),
                PassConfig::TYPE_BEFORE_OPTIMIZATION,
                -1
            );
        }

        if (class_exists(OpenApi::class)) {
            $container->addCompilerPass(new JmsDoctrineObjectConstructionCompilerPass());
        }

        if ($container->hasExtension('security')) {
            /** @var SecurityExtension $extension */
            $extension = $container->getExtension('security');

            if (class_exists(JwtAuthenticator::class)) {
                $extension->addAuthenticatorFactory(new JwtAuthenticatorFactory());
            }

            if (class_exists(MessageAuthenticator::class)) {
                $extension->addAuthenticatorFactory(new MessengerMessageAuthenticatorFactory());
            }
        }
    }
}
