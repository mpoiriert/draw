<?php

namespace Draw\Component\Messenger\Tests\Searchable\Stamp;

use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @covers \Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp
 */
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

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            StampInterface::class,
            $this->entity
        );
    }

    public function testGetTags(): void
    {
        static::assertSame(
            $this->tags,
            $this->entity->getTags()
        );
    }

    public function testGetEnforceUniqueness(): void
    {
        static::assertFalse($this->entity->getEnforceUniqueness());
    }

    public function testGetEnforceUniquenessTrue(): void
    {
        $this->entity = new SearchableTagStamp(
            $this->tags,
            true
        );

        static::assertTrue($this->entity->getEnforceUniqueness());
    }
}
