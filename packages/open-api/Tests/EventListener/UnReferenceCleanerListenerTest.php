<?php

namespace Draw\Component\OpenApi\Tests\EventListener;

use Draw\Component\OpenApi\Cleaner\UnreferencedCleaner;
use Draw\Component\OpenApi\Event\CleanEvent;
use Draw\Component\OpenApi\EventListener\UnReferenceCleanerListener;

/**
 * @internal
 */
class UnReferenceCleanerListenerTest extends BaseCleanerTestCase
{
    private UnReferenceCleanerListener $object;

    protected function setUp(): void
    {
        $this->object = new UnReferenceCleanerListener([
            new UnreferencedCleaner(),
        ]);
    }

    public static function getFixtureDir(): string
    {
        return 'UnReferenceCleanerListener';
    }

    public function clean(CleanEvent $cleanEvent): void
    {
        $this->object->onClean($cleanEvent);
    }
}
