<?php

namespace Draw\Bundle\UserBundle\DependencyInjection;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\AccountLocker;
use Draw\Bundle\UserBundle\Command\RefreshUserLocksCommand;
use Draw\Bundle\UserBundle\EmailWriter\ForgotPasswordEmailWriter;
use Draw\Bundle\UserBundle\EmailWriter\PasswordChangeRequestedEmailWriter;
use Draw\Bundle\UserBundle\EmailWriter\ToUserEmailWriter;
use Draw\Bundle\UserBundle\EmailWriter\UserOnboardingEmailWriter;
use Draw\Bundle\UserBundle\Entity\LockableUserInterface;
use Draw\Bundle\UserBundle\Entity\SecurityUserInterface;
use Draw\Bundle\UserBundle\Entity\UserLock;
use Draw\Bundle\UserBundle\EventListener\AccountLockerListener;
use Draw\Bundle\UserBundle\EventListener\EncryptPasswordUserEntityListener;
use Draw\Bundle\UserBundle\EventListener\PasswordChangeEnforcerListener;
use Draw\Bundle\UserBundle\EventListener\TwoFactorAuthenticationEntityListener;
use Draw\Bundle\UserBundle\EventListener\TwoFactorAuthenticationListener;
use Draw\Bundle\UserBundle\EventListener\UserRequestInterceptorListener;
use Draw\Bundle\UserBundle\Feed\FlashUserFeed;
use Draw\Bundle\UserBundle\Feed\UserFeedInterface;
use Draw\Bundle\UserBundle\MessageHandler\NewUserSendEmailMessageHandler;
use Draw\Bundle\UserBundle\MessageHandler\PasswordChangeRequestedSendEmailMessageHandler;
use Draw\Bundle\UserBundle\MessageHandler\RefreshUserLockMessageHandler;
use Draw\Bundle\UserBundle\MessageHandler\UserLockLifeCycleMessageHandler;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\AuthCodeMailer;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Email\EmailTwoFactorProvider;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\IndecisiveTwoFactorAuthenticationEnforcer;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\RolesTwoFactorAuthenticationEnforcer;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\TwoFactorAuthenticationEnforcerInterface;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DrawUserExtension extends Extension implements PrependExtensionInterface
{
    private array $excludeEntitiesPath = [];

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $loader = new PhpFileLoader($container, new FileLocator(\dirname(__DIR__).'/Resources/config'));

        $container
            ->setDefinition(
                'draw_user.user_repository',
                new Definition(EntityRepository::class)
            )
            ->setFactory([new Reference('doctrine'), 'getRepository'])
            ->setArgument(0, new Parameter('draw_user.user_entity_class'));

        $container->registerAliasForArgument(
            'draw_user.user_repository',
            EntityRepository::class,
            'drawUserEntityRepository'
        );

        $definition = (new Definition())
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $bundleDirectory = realpath(__DIR__.'/..');

        $exclude = [
            $bundleDirectory.'/vendor/',
            $bundleDirectory.'/DrawUserBundle.php',
            $bundleDirectory.'/Controller/',
            $bundleDirectory.'/DependencyInjection/',
            $bundleDirectory.'/DTO/',
            $bundleDirectory.'/Email/',
            $bundleDirectory.'/EmailWriter/',
            $bundleDirectory.'/Entity/',
            $bundleDirectory.'/Event/',
            $bundleDirectory.'/Exception/',
            $bundleDirectory.'/Message/',
            $bundleDirectory.'/Resources/',
            $bundleDirectory.'/Security/TwoFactorAuthentication/Entity/',
            $bundleDirectory.'/Tests/',
        ];

        $loader->registerClasses(
            $definition,
            'Draw\\Bundle\\UserBundle\\',
            $bundleDirectory,
            $exclude
        );

        $loader->registerClasses(
            $definition->addTag('controller.service_arguments'),
            'Draw\\Bundle\\UserBundle\\Controller\\',
            $bundleDirectory.'/Controller/'
        );

        $container
            ->setAlias(UserFeedInterface::class, FlashUserFeed::class);

        $container
            ->getDefinition(UserRequestInterceptorListener::class)
            ->setArgument(
                '$firewallMap',
                new Reference('security.firewall.map', ContainerInterface::NULL_ON_INVALID_REFERENCE)
            );

        $this->assignParameters($config, $container);

        $this->configureEmailWriters($config['email_writers'], $loader, $container);
        $this->configureAccountLocker($config['account_locker'], $loader, $container);
        $this->configureEnforce2fa($config['enforce_2fa'], $loader, $container);
        $this->configureOnBoarding($config['onboarding'], $loader, $container);
        $this->configureNeedPasswordChangeEnforcer($config['password_change_enforcer'], $loader, $container);

        $userClass = $container->getParameter('draw_user.user_entity_class');
        if (!class_exists($userClass)) {
            throw new RuntimeException(\sprintf('The class [%s] does not exists. Make sure you configured the [%s] node properly.', $userClass, 'draw_user.user_entity_class'));
        }

        if ($config['encrypt_password_listener']['enabled']) {
            $container
                ->getDefinition(EncryptPasswordUserEntityListener::class)
                ->setArgument('$autoGeneratePassword', $config['encrypt_password_listener']['auto_generate_password'])
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'preUpdate'])
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'prePersist'])
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'postPersist'])
                ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'postUpdate']);
        } else {
            $container->removeDefinition(EncryptPasswordUserEntityListener::class);
        }

        $container->setParameter(
            'draw.user.orm.metadata_driver.exclude_paths',
            $this->excludeEntitiesPath
        );

        foreach ($container->getDefinitions() as $id => $definition) {
            if ($definition->hasTag('container.excluded')) {
                $container->removeDefinition($id);
            }
        }
    }

    private function assignParameters(array $config, ContainerBuilder $container): void
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

    private function configureEmailWriters(
        array $config,
        PhpFileLoader $loader,
        ContainerBuilder $container,
    ): void {
        if (!$config['enabled']) {
            return;
        }

        if (!interface_exists(EmailWriterInterface::class)) {
            throw new RuntimeException('The packages [draw/mailer] is needs to have email writers');
        }

        $definition = new Definition();
        $definition
            ->setAutowired(true)
            ->setAutoconfigured(true);

        $loader->registerClasses(
            $definition,
            'Draw\\Bundle\\UserBundle\\EmailWriter\\',
            realpath(__DIR__.'/../EmailWriter'),
        );

        if (!$config['forgot_password']['enabled']) {
            $container->removeDefinition(ForgotPasswordEmailWriter::class);
        } else {
            $container->getDefinition(ForgotPasswordEmailWriter::class)
                ->setArgument('$resetPasswordRoute', new Parameter('draw_user.reset_password_route'))
                ->setArgument('$inviteCreateAccountRoute', new Parameter('draw_user.invite_create_account_route'));
        }

        if (!$config['onboarding']['enabled']) {
            $container->removeDefinition(UserOnboardingEmailWriter::class);
        } else {
            $container
                ->getDefinition(UserOnboardingEmailWriter::class)
                ->setArgument('$messageExpirationDelay', $config['onboarding']['expiration_delay']);
        }

        if (!$config['password_change_requested']['enabled']) {
            $container->removeDefinition(PasswordChangeRequestedEmailWriter::class);
        }

        if (!$config['to_user']['enabled']) {
            $container->removeDefinition(ToUserEmailWriter::class);
        }
    }

    private function configureAccountLocker(
        array $config,
        PhpFileLoader $loader,
        ContainerBuilder $containerBuilder,
    ): void {
        if (!$config['enabled']) {
            $containerBuilder->removeDefinition(RefreshUserLocksCommand::class);
            $containerBuilder->removeDefinition(AccountLockerListener::class);
            $containerBuilder->removeDefinition(RefreshUserLockMessageHandler::class);
            $containerBuilder->removeDefinition(UserLockLifeCycleMessageHandler::class);
            $containerBuilder->removeDefinition(AccountLocker::class);
            $this->excludeEntitiesPath[] = (new \ReflectionClass(UserLock::class))->getFileName();

            return;
        }

        $containerBuilder
            ->getDefinition(AccountLockerListener::class)
            ->setArgument('$accountLockedRoute', $config['account_locked_route']);
    }

    private function configureNeedPasswordChangeEnforcer(
        array $config,
        PhpFileLoader $loader,
        ContainerBuilder $containerBuilder,
    ): void {
        if (!$config['enabled']) {
            $containerBuilder->removeDefinition(PasswordChangeEnforcerListener::class);
            $containerBuilder->removeDefinition(PasswordChangeRequestedSendEmailMessageHandler::class);

            return;
        }

        $containerBuilder
            ->getDefinition(PasswordChangeEnforcerListener::class)
            ->setArgument('$changePasswordRoute', $config['change_password_route']);
    }

    private function configureOnBoarding(
        array $config,
        PhpFileLoader $loader,
        ContainerBuilder $containerBuilder,
    ): void {
        if (!$config['enabled']) {
            $containerBuilder->removeDefinition(NewUserSendEmailMessageHandler::class);
        }
    }

    private function configureEnforce2fa(
        array $config,
        PhpFileLoader $loader,
        ContainerBuilder $containerBuilder,
    ): void {
        if (!$config['enabled']) {
            $containerBuilder->removeDefinition(IndecisiveTwoFactorAuthenticationEnforcer::class);
            $containerBuilder->removeDefinition(RolesTwoFactorAuthenticationEnforcer::class);
            $containerBuilder->removeDefinition(TwoFactorAuthenticationEntityListener::class);
            $containerBuilder->removeDefinition(TwoFactorAuthenticationListener::class);
            $containerBuilder->removeDefinition(AuthCodeMailer::class);
            $containerBuilder->removeDefinition(EmailTwoFactorProvider::class);

            return;
        }

        $userClass = $containerBuilder->getParameter('draw_user.user_entity_class');

        $containerBuilder
            ->getDefinition(TwoFactorAuthenticationEntityListener::class)
            ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'preUpdate'])
            ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'prePersist']);

        $containerBuilder
            ->getDefinition(TwoFactorAuthenticationListener::class)
            ->setArgument('$enableRoute', $config['enable_route'])
            ->setArgument('$allowedRoutes', $config['allowed_routes']);

        if ($config['email']['enabled']) {
            $containerBuilder->setDefinition(
                EmailTwoFactorProvider::class,
                new Definition(EmailTwoFactorProvider::class)
            )
                ->setAutoconfigured(true)
                ->setAutowired(true)
                ->setDecoratedService('scheb_two_factor.security.email.provider', 'draw.user.scheb_two_factor.security.email.provider.inner')
                ->setArgument('$decorated', new Reference('draw.user.scheb_two_factor.security.email.provider.inner'));
        } else {
            $containerBuilder->removeDefinition(AuthCodeMailer::class);
            $containerBuilder->removeDefinition(EmailTwoFactorProvider::class);
        }

        if ($config['enforcing_roles']) {
            $containerBuilder
                ->getDefinition(RolesTwoFactorAuthenticationEnforcer::class)
                ->setArgument('$enforcingRoles', $config['enforcing_roles']);

            $containerBuilder
                ->setAlias(
                    TwoFactorAuthenticationEnforcerInterface::class,
                    RolesTwoFactorAuthenticationEnforcer::class
                );
        } else {
            $containerBuilder->removeDefinition(RolesTwoFactorAuthenticationEnforcer::class);
            $containerBuilder
                ->setAlias(
                    TwoFactorAuthenticationEnforcerInterface::class,
                    IndecisiveTwoFactorAuthenticationEnforcer::class
                );
        }
    }

    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig('draw_user');

        $config = $this->processConfiguration(
            $this->getConfiguration($configs, $container),
            $container->getParameterBag()->resolveValue($configs)
        );

        if ($container->hasExtension('doctrine')) {
            $container->prependExtensionConfig('doctrine', [
                'orm' => [
                    'resolve_target_entities' => [
                        SecurityUserInterface::class => $config['user_entity_class'],
                        LockableUserInterface::class => $config['user_entity_class'],
                    ],
                ],
            ]);
        }

        if (!$config['account_locker']['enabled'] && $container->hasExtension('draw_sonata_integration')) {
            $container->prependExtensionConfig(
                'draw_sonata_integration',
                [
                    'user' => [
                        'user_lock' => [
                            'enabled' => false,
                        ],
                    ],
                ]
            );
        }
    }
}
