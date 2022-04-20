<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use App\Entity\MessengerMessage;
use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\Configuration;
use Draw\Component\Application\Configuration\Entity\Config;
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
            'messenger' => [
                'enabled' => true,
                'queue_names' => [],
                'admin' => [
                    'group' => 'Messenger',
                    'entity_class' => MessengerMessage::class,
                    'controller_class' => CRUDController::class,
                    'icon' => 'fas fa-rss',
                    'label' => 'Message',
                    'pager_type' => 'simple',
                ],
            ],
            'configuration' => [
                'enabled' => false,
                'admin' => [
                    'entity_class' => Config::class,
                    'group' => 'draw.sonata.group.application',
                    'controller_class' => CRUDController::class,
                    'icon' => 'fa fa-server',
                    'label' => 'config',
                    'pager_type' => 'default',
                ],
            ],
        ];
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['messenger' => ['queue_names' => 'test']],
            'Invalid type for path "draw_sonata_integration.messenger.queue_names". Expected array, but got string',
        ];
    }
}
