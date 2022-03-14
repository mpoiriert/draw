<?php

namespace Draw\Bundle\ApplicationBundle\Tests\DependencyInjection;

use Draw\Bundle\ApplicationBundle\Configuration\Entity\Config;
use Draw\Bundle\ApplicationBundle\DependencyInjection\Configuration;
use Draw\Component\Tester\DependencyInjection\ConfigurationTestCase;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class ConfigurationTest extends ConfigurationTestCase
{
    public function createConfiguration(): ConfigurationInterface
    {
        return new Configuration();
    }

    public function getDefaultConfiguration(): array
    {
        return [
            'configuration' => [
                'enabled' => false,
                'sonata' => [
                    'enabled' => false,
                    'entity_class' => Config::class,
                    'group' => 'draw.sonata.group.application',
                    'label' => 'config',
                    'icon' => 'fa fa-server',
                    'controller_class' => CRUDController::class,
                    'pager_type' => 'default',
                ],
            ],
            'versioning' => [
                'enabled' => false,
            ],
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        return [];
    }
}
