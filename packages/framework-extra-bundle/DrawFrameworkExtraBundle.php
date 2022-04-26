<?php

namespace Draw\Bundle\FrameworkExtraBundle;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddCommandExecutionOptionsCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\AddNewestInstanceRoleCommandOptionPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\EmailWriterCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerBrokerCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\MessengerTransportNamesCompilerPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security\JwtAuthenticatorFactory;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security\MessengerMessageAuthenticatorFactory;
use Draw\Component\Console\Listener\CommandFlowListener;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Draw\Component\Messenger\Broker;
use Draw\Component\Messenger\Command\PurgeExpiredMessageCommand;
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
        if (class_exists(EventDrivenUserChecker::class)) {
            $container->addCompilerPass(new UserCheckerDecoratorPass());
        }

        if (class_exists(Broker::class)) {
            $container->addCompilerPass(new MessengerBrokerCompilerPass());
        }

        if (class_exists(AddNewestInstanceRoleCommandOptionPass::class)) {
            $container->addCompilerPass(new AddNewestInstanceRoleCommandOptionPass());
        }

        if (class_exists(CommandFlowListener::class)) {
            $container->addCompilerPass(new AddCommandExecutionOptionsCompilerPass());
        }

        if (interface_exists(EmailWriterInterface::class)) {
            $container->addCompilerPass(new EmailWriterCompilerPass());
        }

        if (class_exists(PurgeExpiredMessageCommand::class)) {
            $container->addCompilerPass(
                new MessengerTransportNamesCompilerPass(),
                PassConfig::TYPE_BEFORE_OPTIMIZATION,
                -1
            );
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
