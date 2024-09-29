<?php

namespace App\Tests\Feature;

use App\Feature\DemoFeature;
use App\Tests\TestCase;

/**
 * @internal
 */
class DemoFeatureTest extends TestCase
{
    private DemoFeature $object;

    protected function setUp(): void
    {
        $this->object = static::getContainer()->get(DemoFeature::class);
    }

    public function testGetIsEnabled(): void
    {
        static::assertFalse(
            $this->object->isEnabled(),
            'The value is coming from the DB'
        );
    }

    public function testGetLimit(): void
    {
        static::assertSame(
            10,
            $this->object->getLimit(),
            'The value is coming from the DB'
        );
    }
}
