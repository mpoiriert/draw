<?php

namespace Draw\Component\Validator\DependencyInjection;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\DependencyInjection\Integration\IntegrationInterface;
use Draw\Component\DependencyInjection\Integration\IntegrationTrait;
use Draw\Component\Validator\Constraints\ValueIsNotUsedValidator;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class ValidatorIntegration implements IntegrationInterface
{
    use IntegrationTrait;

    public function getConfigSectionName(): string
    {
        return 'validator';
    }

    public function load(array $config, PhpFileLoader $loader, ContainerBuilder $container): void
    {
        if (!class_exists(ValueIsNotUsedValidator::class)) {
            return;
        }

        $this->registerClasses(
            $loader,
            $namespace = 'Draw\Component\Validator\Constraints\\',
            \dirname((new \ReflectionClass(ValueIsNotUsedValidator::class))->getFileName()).'/*Validator.php'
        );

        if (!interface_exists(ManagerRegistry::class)) {
            $container->removeDefinition(ValueIsNotUsedValidator::class);
        }

        $this->renameDefinitions(
            $container,
            $namespace,
            'draw.validator.constraints.'
        );
    }

    public function addConfiguration(ArrayNodeDefinition $node): void
    {
        // nothing to do
    }
}
