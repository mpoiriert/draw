<?php

namespace Draw\Bundle\UserBundle\DependencyInjection;

use Doctrine\ORM\EntityRepository;
use Draw\Bundle\UserBundle\Jwt\JwtAuthenticator;
use Draw\Bundle\UserBundle\Listener\EncryptPasswordUserEntityListener;
use Draw\Bundle\UserBundle\Onboarding\EmailWriter\UserOnboardingEmailWriter;
use Draw\Bundle\UserBundle\Onboarding\MessageHandler\NewUserSendEmailMessageHandler;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\EmailWriter\PasswordChangeRequestedEmailWriter;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\Listener\PasswordChangeEnforcerSubscriber;
use Draw\Bundle\UserBundle\PasswordChangeEnforcer\MessageHandler\PasswordChangeRequestedSendEmailMessageHandler;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\IndecisiveTwoFactorAuthenticationEnforcer;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\RolesTwoFactorAuthenticationEnforcer;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Enforcer\TwoFactorAuthenticationEnforcerInterface;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Listener\TwoFactorAuthenticationEntityListener;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\Listener\TwoFactorAuthenticationSubscriber;
use Draw\Bundle\UserBundle\Security\TwoFactorAuthentication\TwoFactorAuthenticationUserInterface;
use Draw\Bundle\UserBundle\Sonata\Controller\TwoFactorAuthenticationController;
use Draw\Bundle\UserBundle\Sonata\Extension\TwoFactorAuthenticationExtension;
use Draw\Bundle\UserBundle\Sonata\Extension\TwoFactorAuthenticationExtension3X;
use Draw\Bundle\UserBundle\Sonata\Extension\TwoFactorAuthenticationExtension4X;
use ReflectionClass;
use RuntimeException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DrawUserExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);

        $container->registerAliasForArgument(
            'draw_user.user_repository',
            EntityRepository::class,
            'drawUserEntityRepository'
        );

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $this->assignParameters($config, $container);

        $this->configureSonata($config['sonata'], $loader, $container);
        $this->configureEmailWriters($config['email_writers'], $loader, $container);
        $this->configureEnforce2fa($config['enforce_2fa'], $loader, $container);
        $this->configurePasswordRecovery($config['password_recovery'], $loader, $container);
        $this->configureJwtAuthenticator($config['jwt_authenticator'], $loader, $container);
        $this->configureOnboarding($config['onboarding'], $loader, $container);
        $this->configureNeedPasswordChangeEnforcer($config['password_change_enforcer'], $loader, $container);

        $userClass = $container->getParameter('draw_user.user_entity_class');
        if (!class_exists($userClass)) {
            throw new RuntimeException(sprintf('The class [%s] does not exists. Make sure you configured the [%s] node properly.', $userClass, 'draw_user.user_entity_class'));
        }

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

    private function configureNeedPasswordChangeEnforcer(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $containerBuilder
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('password-change-enforcer.xml');

        $containerBuilder->getDefinition(PasswordChangeEnforcerSubscriber::class)
            ->setArgument('$changePasswordRoute', $config['change_password_route']);

        if (!$config['email']['enabled']) {
            $containerBuilder->removeDefinition(PasswordChangeRequestedSendEmailMessageHandler::class);
        } else {
            $this->checkEmailWriter($containerBuilder, 'password_change_enforcer');
        }
    }

    private function configureOnBoarding(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $containerBuilder
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('onboarding.xml');

        if (!$config['email']['enabled']) {
            $containerBuilder->removeDefinition(NewUserSendEmailMessageHandler::class);
        } else {
            $this->checkEmailWriter($containerBuilder, 'onboarding');
            $containerBuilder->getDefinition(UserOnboardingEmailWriter::class)
                ->setArgument('$messageExpirationDelay', $config['email']['expiration_delay']);
        }
    }

    private function configureEnforce2fa(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $containerBuilder
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('enforce-2fa.xml');

        $userClass = $containerBuilder->getParameter('draw_user.user_entity_class');

        $containerBuilder->getDefinition(TwoFactorAuthenticationEntityListener::class)
            ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'preUpdate'])
            ->addTag('doctrine.orm.entity_listener', ['entity' => $userClass, 'event' => 'prePersist']);

        $containerBuilder->getDefinition(TwoFactorAuthenticationSubscriber::class)
            ->setArgument('$enableRoute', $config['enable_route']);

        if ($config['enforcing_roles']) {
            $containerBuilder->getDefinition(RolesTwoFactorAuthenticationEnforcer::class)
                ->setArgument('$enforcingRoles', $config['enforcing_roles']);

            $containerBuilder
                ->setAlias(
                    TwoFactorAuthenticationEnforcerInterface::class,
                    RolesTwoFactorAuthenticationEnforcer::class
                );

            return;
        }

        $containerBuilder
            ->setAlias(
                TwoFactorAuthenticationEnforcerInterface::class,
                IndecisiveTwoFactorAuthenticationEnforcer::class
            );
    }

    private function configurePasswordRecovery(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $containerBuilder
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $loader->load('password-recovery.xml');

        if (!$config['email']['enabled']) {
            $containerBuilder->removeDefinition(PasswordChangeRequestedEmailWriter::class);
        } else {
            $this->checkEmailWriter($containerBuilder, 'password_recovery');
        }
    }

    private function configureEmailWriters(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $containerBuilder
    ): void {
        if (!$config['enabled']) {
            return;
        }

        $this->checkEmailWriter($containerBuilder, 'email_writers');

        $loader->load('email-writers.xml');
    }

    private function configureSonata(array $config, LoaderInterface $loader, ContainerBuilder $container): void
    {
        if (!$config['enabled']) {
            return;
        }

        $container->setParameter('draw_user.sonata.user_admin_code', $config['user_admin_code']);
        $loader->load('sonata.xml');

        if (!$config['2fa']['enabled'] ?? false) {
            $container->removeDefinition(TwoFactorAuthenticationExtension::class);
            $container->removeDefinition(TwoFactorAuthenticationController::class);
        } else {
            if (!isset($container->getParameter('kernel.bundles')['SchebTwoFactorBundle'])) {
                throw new RuntimeException('The bundle SchebTwoFactorBundle needs to be registered to have 2FA enabled.');
            }

            $reflectionClass = new ReflectionClass($userEntityClass = $container->getParameter('draw_user.user_entity_class'));
            if (!$reflectionClass->implementsInterface(TwoFactorAuthenticationUserInterface::class)) {
                throw new RuntimeException(sprintf('The class [%s] must implements [%s] to have 2FA enabled.', $userEntityClass, TwoFactorAuthenticationUserInterface::class));
            }

            $type = (new \ReflectionClass(AbstractAdmin::class))
                ->getMethod('configureRoutes')
                ->getParameters()[0]->getType()->getName();
            // TODO remove ExecutionAdmin3X when stop support of sonata admin 3.x
            $extensionClass = RouteCollectionInterface::class === $type
                ? TwoFactorAuthenticationExtension4X::class
                : TwoFactorAuthenticationExtension3X::class;

            $container->getDefinition(TwoFactorAuthenticationExtension::class)
                ->setClass($extensionClass)
                ->setArgument(0, $config['2fa']['field_positions'])
                ->addTag('sonata.admin.extension', ['target' => $config['user_admin_code']]);
        }
    }

    private function configureJwtAuthenticator(
        array $config,
        LoaderInterface $loader,
        ContainerBuilder $container
    ): void {
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

    private function checkEmailWriter(ContainerBuilder $containerBuilder, string $for): void
    {
        if (!isset($containerBuilder->getParameter('kernel.bundles')['DrawPostOfficeBundle'])) {
            throw new RuntimeException(sprintf('The bundle [%s] needs to be registered to have email enabled for [%s].', $for, 'DrawPostOfficeBundle'));
        }
    }
}
