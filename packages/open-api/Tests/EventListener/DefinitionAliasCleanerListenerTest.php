<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Event\CleanEvent;
use Draw\Component\OpenApi\EventListener\DefinitionAliasCleanerListener;

class DefinitionAliasCleanerListenerTest extends BaseCleanerTestCase
{
    private DefinitionAliasCleanerListener $object;

    protected function setUp(): void
    {
        $this->object = new DefinitionAliasCleanerListener();
    }

    public static function getFixtureDir(): string
    {
        return 'DefinitionAliasCleanerListener';
    }

    public function clean(CleanEvent $cleanEvent): void
    {
        $this->object->onClean($cleanEvent);
    }
}
