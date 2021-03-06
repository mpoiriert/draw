<?php

namespace Draw\Bundle\UserBundle\DependencyInjection;

use Draw\Bundle\UserBundle\Jwt\JwtAuthenticator;
use Draw\Bundle\UserBundle\Listener\EncryptPasswordUserEntityListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

class DrawUserExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->configureSonata($config['sonata'], $loader, $container);
        $this->configureJwtAuthenticator($config['jwt_authenticator'], $loader, $container);

        $userClass = $config['user_entity_class'];
        if (!class_exists($userClass)) {
            throw new \RuntimeException(sprintf('The class [%s] does not exists. Make sure you configured the [%s] node properly.', $userClass, 'draw_user.user_entity_class'));
        }

        $this->assignParameters($config, $container);

        if ($config['encrypt_password_listener']['enabled']) {
            $container->getDefinition(EncryptPasswordUserEntityListener::class)
                ->setArgument('$autoGeneratePassword', $config['encrypt_password_listener']['auto_generate_password'])
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'preUpdate'])
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'prePersist'])
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'postPersist'])
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'postUpdate']);
        } else {
            $container->removeDefinition(EncryptPasswordUserEntityListener::class);
        }
    }

    private function assignParameters($config, ContainerBuilder $container)
    {
        $parameterNames = [
            'user_entity_class',
            'reset_password_route',
            'invite_create_account_route',
        ];

        foreach ($parameterNames as $parameterName) {
            $container->setParameter('draw_user.'.$parameterName, $config[$parameterName]);
        }
    }

    private function configureSonata(array $config, LoaderInterface $loader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            return;
        }

        $container->setParameter('draw_user.sonata.user_admin_code', $config['user_admin_code']);
        $loader->load('sonata.xml');
    }

    private function configureJwtAuthenticator(array $config, LoaderInterface $loader, ContainerBuilder $container)
    {
        if (!$config['enabled']) {
            $container->removeDefinition(JwtAuthenticator::class);

            return;
        }

        $definition = $container
            ->getDefinition(JwtAuthenticator::class);

        $definition->setArgument('$key', $config['key']);

        if (!$config['query_parameters']['enabled']) {
            return;
        }

        $definition->setArgument('$queryParameters', $config['query_parameters']['accepted_keys']);
    }
}
