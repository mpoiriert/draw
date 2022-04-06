<?php

namespace Draw\Bundle\UserBundle;

use Draw\Bundle\UserBundle\DependencyInjection\Factory\Security\AdminLoginFactory;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawUserBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        if ($container->hasExtension('security')) {
            /** @var SecurityExtension $extension */
            $extension = $container->getExtension('security');
            $extension->addAuthenticatorFactory(new AdminLoginFactory());
        }
    }
}
