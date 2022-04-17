<?php

namespace Draw\Bundle\FrameworkExtraBundle\Tests\DependencyInjection;

use Draw\Component\Messenger\Broker;
use Draw\Component\Messenger\Message\AsyncHighPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncLowPriorityMessageInterface;
use Draw\Component\Messenger\Message\AsyncMessageInterface;
use ReflectionClass;

class DrawFrameworkExtraExtensionMessengerTest extends DrawFrameworkExtraExtensionTest
{
    public function getConfiguration(): array
    {
        return [
            'messenger' => [
                'async_routing_configuration' => [
                    'enabled' => true,
                ],
            ],
        ];
    }

    public function testPrepend(): void
    {
        $containerBuilder = static::getContainerBuilder();

        $containerBuilder->registerExtension($this->getExtension());

        $containerBuilder->loadFromExtension('draw_framework_extra', $this->getConfiguration());

        $this->getExtension()->prepend($containerBuilder);

        $result = $containerBuilder
            ->getExtensionConfig('framework');

        $installationPath = dirname((new ReflectionClass(Broker::class))->getFileName());

        $this->assertSame(
            [
                [
                    'messenger' => [
                        'routing' => [
                            AsyncMessageInterface::class => 'async',
                            AsyncHighPriorityMessageInterface::class => 'async_high_priority',
                            AsyncLowPriorityMessageInterface::class => 'async_low_priority',
                        ],
                    ],
                ],
                [
                    'translator' => [
                        'paths' => [
                            'draw-messenger' => $installationPath.'/Resources/translations',
                        ],
                    ],
                ],
            ],
            $result
        );
    }
}
