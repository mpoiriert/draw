<?php

namespace Draw\Bundle\UserBundle;

use Draw\Bundle\UserBundle\DependencyInjection\Compiler\UserCheckerDecoratorPass;
use Draw\Bundle\UserBundle\DependencyInjection\Factory\Security\AdminLoginFactory;
use Draw\Bundle\UserBundle\DependencyInjection\Factory\Security\JwtAuthenticatorFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(
            new UserCheckerDecoratorPass(),
            PassConfig::TYPE_BEFORE_OPTIMIZATION
        );

        if ($container->hasExtension('security')) {
            /** @var SecurityExtension $extension */
            $extension = $container->getExtension('security');
            $extension->addAuthenticatorFactory(new AdminLoginFactory());
            $extension->addAuthenticatorFactory(new JwtAuthenticatorFactory());
        }
    }
}
