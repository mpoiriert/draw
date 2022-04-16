<?php

namespace Draw\Component\Messenger\Tests\Stamp;

use Draw\Component\Messenger\Stamp\SearchableTagStamp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Stamp\StampInterface;

/**
 * @covers \Draw\Component\Messenger\Stamp\SearchableTagStamp
 */
class SearchableTagStampTest extends TestCase
{
    private SearchableTagStamp $entity;

    private array $tags;

    public function setUp(): void
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
        $this->assertInstanceOf(
            StampInterface::class,
            $this->entity
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
