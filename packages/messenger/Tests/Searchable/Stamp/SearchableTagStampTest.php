<?php

namespace Draw\Component\Messenger\Tests\Searchable\Stamp;

use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(SearchableTagStamp::class)]
class SearchableTagStampTest extends TestCase
{
    private SearchableTagStamp $entity;

    private array $tags;

    protected function setUp(): void
    {
        $this->entity = new SearchableTagStamp(
            $this->tags = [
                uniqid('tag-1'),
                uniqid('tag-2'),
            ]
        );
    }

    public function testGetTags(): void
    {
        $this->assertSame(
            $this->tags,
            $this->entity->getTags()
        );
    }

    public function testGetEnforceUniqueness(): void
    {
        $this->assertFalse($this->entity->getEnforceUniqueness());
    }

    public function testGetEnforceUniquenessTrue(): void
    {
        $this->entity = new SearchableTagStamp(
            $this->tags,
            true
        );

        $this->assertTrue($this->entity->getEnforceUniqueness());
    }
}
