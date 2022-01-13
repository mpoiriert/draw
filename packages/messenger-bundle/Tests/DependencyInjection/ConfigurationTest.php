<?php

namespace Draw\Bundle\MessengerBundle\Tests\DependencyInjection;

use App\Entity\MessengerMessage;
use App\Entity\MessengerMessageTag;
use Draw\Bundle\MessengerBundle\DependencyInjection\Configuration;
use Draw\Component\Tester\DependencyInjection\ConfigurationTestCase;
use Draw\Contracts\Application\VersionVerificationInterface;
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
            'broker' => [
                'enabled' => false,
                'symfony_console_path' => null,
                'receivers' => [],
                'default_options' => [],
            ],
            'worker_version_monitoring' => [
                'enabled' => false,
                'version_verification_service' => VersionVerificationInterface::class,
            ],
            'sonata' => [
                'enabled' => false,
                'transports' => [],
                'entity_class' => MessengerMessage::class,
                'tag_entity_class' => MessengerMessageTag::class,
                'group' => 'Messenger',
                'controller_class' => 'Sonata\AdminBundle\Controller\CRUDController',
                'icon' => 'fa fa-rss',
                'label' => 'Message',
                'pager_type' => 'simple',
            ],
            'transport_service_name' => 'messenger.transport.draw',
        ];
    }

    public function testBrokerEnabledConfiguration(): void
    {
        $config = $this->processConfiguration([
            [
                'broker' => [
                    'symfony_console_path' => 'test',
                    'receivers' => ['sync'],
                    'default_options' => [
                        'as-system' => null,
                        'sleep' => 60,
                        'limit' => 1,
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            [
                'enabled' => true,
                'symfony_console_path' => 'test',
                'receivers' => ['sync'],
                'default_options' => [
                    'as-system' => [
                        'name' => 'as-system',
                        'value' => null,
                    ],
                    'sleep' => [
                        'name' => 'sleep',
                        'value' => 60,
                    ],
                    'limit' => [
                        'name' => 'limit',
                        'value' => 1,
                    ],
                ],
            ],
            $config['broker']
        );
    }

    public function provideTestInvalidConfiguration(): iterable
    {
        yield [
            ['transport_service_name' => []],
            'Invalid type for path "draw_messenger.transport_service_name". Expected scalar, but got array.',
        ];
    }
}
