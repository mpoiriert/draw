<?php

namespace Draw\Bundle\UserBundle;

use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\Factory\Security\AdminLoginFactory;
use Draw\Bundle\UserBundle\DependencyInjection\Compiler\ExcludeDoctrineEntitiesCompilerPass;
use Symfony\Bundle\SecurityBundle\DependencyInjection\SecurityExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrawUserBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new ExcludeDoctrineEntitiesCompilerPass());

        if ($container->hasExtension('security')) {
            /** @var SecurityExtension $extension */
            $extension = $container->getExtension('security');
            $extension->addAuthenticatorFactory(new AdminLoginFactory());
        }
    }
}
