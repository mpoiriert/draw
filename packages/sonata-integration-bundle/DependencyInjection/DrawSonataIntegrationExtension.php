<?php

namespace Draw\Bundle\SonataIntegrationBundle\DependencyInjection;

use Draw\Bundle\SonataExtraBundle\Configuration\SonataAdminNodeConfiguration;
use Draw\Bundle\SonataIntegrationBundle\Configuration\Admin\ConfigAdmin;
use Draw\Bundle\SonataIntegrationBundle\Console\Admin\ExecutionAdmin;
use Draw\Bundle\SonataIntegrationBundle\Console\Command;
use Draw\Bundle\SonataIntegrationBundle\Console\CommandRegistry;
use Draw\Bundle\SonataIntegrationBundle\Messenger\Admin\MessengerMessageAdmin;
use Draw\Bundle\SonataIntegrationBundle\User\Action\RequestPasswordChangeAction;
use Draw\Bundle\SonataIntegrationBundle\User\Action\UnlockUserAction;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\Extension\PasswordChangeEnforcerExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\Extension\UserLockExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Admin\UserLockAdmin;
use Draw\Bundle\SonataIntegrationBundle\User\Block\UserCountBlock;
use Draw\Bundle\SonataIntegrationBundle\User\Controller\LoginController;
use Draw\Bundle\SonataIntegrationBundle\User\Controller\RefreshUserLockController;
use Draw\Bundle\SonataIntegrationBundle\User\Controller\TwoFactorAuthenticationController;
use Draw\Bundle\SonataIntegrationBundle\User\Extension\TwoFactorAuthenticationExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Twig\UserAdminExtension;
use Draw\Bundle\SonataIntegrationBundle\User\Twig\UserAdminRuntime;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;
use ReflectionClass;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Parameter;

class DrawSonataIntegrationExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $this->configureConfiguration($config['configuration'], $loader, $container);
        $this->configureConsole($config['console'], $loader, $container);
        $this->configureMessenger($config['messenger'], $loader, $container);
        $this->configureUser($config['user'], $loader, $container);
    }

    private function configureConfiguration(
        array $config,
        Loader\FileLoader $loader,
        ContainerBuilder $container
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $container
            ->setDefinition(
                ConfigAdmin::class,
                SonataAdminNodeConfiguration::configureFromConfiguration(
                    new Definition(ConfigAdmin::class),
                    $config['admin']
                )
            )
            ->addMethodCall(
                'setTranslationDomain',
                ['DrawConfigurationSonata']
            );
    }

    private function configureConsole(array $config, Loader\FileLoader $loader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container
            ->setDefinition(
                ExecutionAdmin::class,
                SonataAdminNodeConfiguration::configureFromConfiguration(
                    new Definition(ExecutionAdmin::class),
                    $config['admin']
                )
            )
            ->setAutowired(true)
            ->setAutoconfigured(true);

        if (!$container->hasDefinition($config['admin']['controller_class'])) {
            $container->setDefinition(
                $config['admin']['controller_class'],
                (new Definition($config['admin']['controller_class']))
                    ->setAutoconfigured(true)
                    ->setAutowired(true)
                    ->addTag('controller.service_arguments')
            );
        }

        $definition = $container
            ->setDefinition(
                CommandRegistry::class,
                (new Definition(CommandRegistry::class))
                    ->setAutowired(true)
                    ->setAutoconfigured(true)
            );

        foreach ($config['commands'] as $configuration) {
            $definition->addMethodCall(
                'setCommand',
                [
                    (new Definition(Command::class))
                        ->setArguments($this->arrayToArgumentsArray($configuration)),
                ]
            );
        }
    }

    private function configureMessenger(array $config, Loader\FileLoader $loader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container
            ->setDefinition(
                MessengerMessageAdmin::class,
                SonataAdminNodeConfiguration::configureFromConfiguration(
                    new Definition(MessengerMessageAdmin::class),
                    $config['admin']
                )
            )
            ->setAutowired(true)
            ->setAutoconfigured(true)
            ->addMethodCall(
                'setTemplate',
                ['show', '@DrawSonataIntegration/Messenger/Message/show.html.twig']
            )
            ->setBindings([
                '$queueNames' => $config['queue_names'],
            ]);
    }

    private function configureUser(array $config, Loader\FileLoader $loader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container->setParameter('draw_user.sonata.user_admin_code', $config['user_admin_code']);

        $container
            ->setDefinition(
                LoginController::class,
                new Definition(LoginController::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->addTag('controller.service_arguments');

        $container
            ->setDefinition(
                UserCountBlock::class,
                new Definition(UserCountBlock::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArgument('$userAdminCode', new Parameter('draw_user.sonata.user_admin_code'))
            ->addTag('sonata.block');

        $container
            ->setDefinition(
                UserAdminExtension::class,
                new Definition(UserAdminExtension::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true);

        $container
            ->setDefinition(
                UserAdminRuntime::class,
                new Definition(UserAdminRuntime::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArgument('$userAdminCode', new Parameter('draw_user.sonata.user_admin_code'));

        $container
            ->setDefinition(
                PasswordChangeEnforcerExtension::class,
                new Definition(PasswordChangeEnforcerExtension::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->addTag('sonata.admin.extension', ['target' => $config['user_admin_code']]);

        $container
            ->setDefinition(
                'draw.sonata.user.action.request_password_change_action',
                new Definition(RequestPasswordChangeAction::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->addTag('controller.service_arguments');

        $this->configureUserLock($config['user_lock'], $loader, $container);

        if (!$config['2fa']['enabled']) {
            return;
        }

        if (!isset($container->getParameter('kernel.bundles')['SchebTwoFactorBundle'])) {
            throw new RuntimeException('The bundle SchebTwoFactorBundle needs to be registered to have 2FA enabled.');
        }

        $reflectionClass = new ReflectionClass($userEntityClass = $container->getParameter('draw_user.user_entity_class'));
        if (!$reflectionClass->implementsInterface(TwoFactorAuthenticationUserInterface::class)) {
            throw new RuntimeException(sprintf('The class [%s] must implements [%s] to have 2FA enabled.', $userEntityClass, TwoFactorAuthenticationUserInterface::class));
        }

        $container
            ->setDefinition(
                TwoFactorAuthenticationExtension::class,
                new Definition(TwoFactorAuthenticationExtension::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->setArgument(0, $config['2fa']['field_positions'])
            ->addTag('sonata.admin.extension', ['target' => $config['user_admin_code']]);

        $container
            ->setDefinition(
                TwoFactorAuthenticationController::class,
                new Definition(TwoFactorAuthenticationController::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->addTag('controller.service_arguments');
    }

    private function configureUserLock(array $config, Loader\FileLoader $loader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container
            ->setDefinition(
                UserLockAdmin::class,
                SonataAdminNodeConfiguration::configureFromConfiguration(
                    new Definition(UserLockAdmin::class),
                    $config['admin']
                )
            );

        $container
            ->setDefinition(
                UserLockExtension::class,
                new Definition(UserLockExtension::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->addTag(
                'sonata.admin.extension',
                ['target' => $container->getParameter('draw_user.sonata.user_admin_code')]
            );

        $container
            ->setDefinition(
                'draw.sonata.user.action.unlock_user_action',
                new Definition(UnlockUserAction::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->addTag('controller.service_arguments');

        $container
            ->setDefinition(
                RefreshUserLockController::class,
                new Definition(RefreshUserLockController::class)
            )
            ->setAutoconfigured(true)
            ->setAutowired(true)
            ->addTag('controller.service_arguments');
    }

    private function arrayToArgumentsArray(array $arguments): array
    {
        $result = [];
        foreach ($arguments as $key => $value) {
            $result['$'.$key] = $value;
        }

        return $result;
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('draw_sonata_integration');

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

        $this->prependUser($config['user'], $container);
    }

    private function prependUser(array $config, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        if ($container->hasExtension('sonata_admin')) {
            $container->prependExtensionConfig(
                'sonata_admin',
                [
                    'templates' => [
                        'user_block' => '@DrawSonataIntegration/User/Block/user_block.html.twig',
                    ],
                ]
            );
        }

        if ($container->hasExtension('sonata_block')) {
            $container->prependExtensionConfig(
                'sonata_block',
                [
                    'blocks' => [
                        UserCountBlock::class => null,
                    ],
                ]
            );
        }
    }
}
