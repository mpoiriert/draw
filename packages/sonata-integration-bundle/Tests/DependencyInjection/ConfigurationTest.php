<?php

namespace Draw\Bundle\SonataIntegrationBundle\Tests\DependencyInjection;

use App\Entity\MessengerMessage;
use Draw\Bundle\SonataIntegrationBundle\DependencyInjection\Configuration;
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
