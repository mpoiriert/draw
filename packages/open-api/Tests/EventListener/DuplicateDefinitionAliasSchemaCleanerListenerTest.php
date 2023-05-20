<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Event\CleanEvent;
use Draw\Component\OpenApi\EventListener\DuplicateDefinitionAliasSchemaCleanerListener;

/**
 * @covers \Draw\Component\OpenApi\EventListener\DuplicateDefinitionAliasSchemaCleanerListener
 */
class DuplicateDefinitionAliasSchemaCleanerListenerTest extends BaseCleanerTestCase
{
    private DuplicateDefinitionAliasSchemaCleanerListener $object;

    protected function setUp(): void
    {
        $this->object = new DuplicateDefinitionAliasSchemaCleanerListener();
    }

    public function getFixtureDir(): string
    {
        return 'DuplicateDefinitionAliasSchemaCleanerListener';
    }

    public function clean(CleanEvent $cleanEvent): void
    {
        $this->object->onClean($cleanEvent);
    }
}
