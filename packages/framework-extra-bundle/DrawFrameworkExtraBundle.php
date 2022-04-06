<?php

namespace Draw\Bundle\FrameworkExtraBundle;

use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security\JwtAuthenticatorFactory;
use Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Factory\Security\MessengerMessageAuthenticatorFactory;
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
            $container->addCompilerPass(
                new UserCheckerDecoratorPass(),
                PassConfig::TYPE_BEFORE_OPTIMIZATION
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
