<?php

namespace Draw\Bundle\FrameworkExtraBundle\DependencyInjection\Integration;

use Draw\Component\Security\Core\Authentication\SystemAuthenticator;
use Draw\Component\Security\Core\Authentication\SystemAuthenticatorInterface;
use Draw\Component\Security\Core\Listener\SystemConsoleAuthenticatorListener;
use Draw\Component\Security\Http\Authenticator\JwtAuthenticator;
use ReflectionClass;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class SecurityIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'security';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->loadCore($config, $loader, $container);
        $this->loadHttp($config, $loader, $container);
    }

    private function loadCore(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\Security\\Core\\',
            $directory = dirname(
                (new ReflectionClass(SystemAuthenticatorInterface::class))->getFileName(),
                2
            ),
            [
                $directory.'/Authentication/Token/',
                $directory.'/User/EventDrivenUserChecker.php',
            ]
        );

        if (!$this->isConfigEnabled($container, $config['system_authentication'])) {
            $container->removeDefinition(SystemAuthenticator::class);
        } else {
            $container->getDefinition(SystemAuthenticator::class)
                ->setArgument('$roles', $config['system_authentication']['roles']);

            $container
                ->setAlias(
                    SystemAuthenticatorInterface::class,
                    SystemAuthenticator::class
                );
        }

        if (!$this->isConfigEnabled($container, $config['console_authentication'])) {
            $container->removeDefinition(SystemConsoleAuthenticatorListener::class);
        } else {
            $container->getDefinition(SystemConsoleAuthenticatorListener::class)
                ->setArgument('$systemAutoLogin', $config['console_authentication']['system_auto_login']);
        }

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.security.core.'
        );
    }

    private function loadHttp(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        $this->registerClasses(
            $loader,
            $namespace = 'Draw\\Component\\Security\\Http\\',
            $directory = dirname(
                (new ReflectionClass(JwtAuthenticator::class))->getFileName(),
                2
            ),
            [
                $directory.'/Authenticator/Passport/Badge/',
                $directory.'/Authenticator/*Authenticator.php',
            ]
        );

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.security.http.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        $node
             ->children()
                ->arrayNode('system_authentication')
                    ->canBeEnabled()
                    ->children()
                        ->arrayNode('roles')
                            ->addDefaultChildrenIfNoneSet()
                            ->scalarPrototype()->defaultValue('ROLE_SYSTEM')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('console_authentication')
                    ->canBeEnabled()
                    ->children()
                        ->booleanNode('system_auto_login')->defaultValue(false)->end()
                    ->end()
                ->end()
            ->end();
    }
}
