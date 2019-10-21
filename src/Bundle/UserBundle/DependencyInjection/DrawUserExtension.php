<?php namespace Draw\Bundle\UserBundle\DependencyInjection;

use Draw\Bundle\UserBundle\Listener\EncryptPasswordUserEntityListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader;

class DrawUserExtension extends ConfigurableExtension
{
    protected function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');

        if ($config['sonata']['enabled']) {
            $container->setParameter('draw_user.sonata.user_admin_code', $config['sonata']['user_admin_code']);
            $loader->load('sonata.yaml');
        }

        if ($config['encrypt_password_listener']) {
            $userClass = $config['user_entity_class'];
            if (!class_exists($userClass)) {
                throw new \RuntimeException(sprintf(
                    'The class [%s] does not exists. Make sure you configured the [%s] node properly.',
                    $userClass,
                    'draw_user.user_entity_class'
                ));
            }
            $container->getDefinition(EncryptPasswordUserEntityListener::class)
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'preUpdate'])
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'prePersist'])
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'postUpdate']);
        } else {
            $container->removeDefinition(EncryptPasswordUserEntityListener::class);
        }
    }
}