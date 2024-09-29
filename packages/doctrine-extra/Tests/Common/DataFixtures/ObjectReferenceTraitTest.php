<?php

namespace Draw\DoctrineExtra\Tests\Common\DataFixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Draw\DoctrineExtra\Common\DataFixtures\ObjectReferenceTrait;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class ObjectReferenceTraitTest extends TestCase
{
    /**
     * @var object|ObjectReferenceTrait
     */
    private object $trait;

    /**
     * @var ReferenceRepository&MockObject
     */
    private ReferenceRepository $referenceRepository;

    protected function setUp(): void
    {
        $this->referenceRepository = $this->createMock(ReferenceRepository::class);

        $this->trait = new class($this->referenceRepository) {
            use ObjectReferenceTrait;

            protected ReferenceRepository $referenceRepository;

            public function __construct(ReferenceRepository $referenceRepository)
            {
                $this->referenceRepository = $referenceRepository;
            }
        };
    }

    public function testAddObjectReference(): void
    {
        $this->referenceRepository
            ->expects(static::once())
            ->method('addReference')
            ->with(
                \sprintf(
                    '%s.%s',
                    $class = uniqid('class-'),
                    $name = uniqid('name-')
                ),
                $object = new \stdClass()
            )
        ;

        $this->trait->addObjectReference($class, $name, $object);
    }

    public function testHasObjectReference(): void
    {
        $this->referenceRepository
            ->expects(static::once())
            ->method('hasReference')
            ->with(
                \sprintf(
                    '%s.%s',
                    $class = uniqid('class-'),
                    $name = uniqid('name-')
                )
            )
            ->willReturn(false)
        ;

        static::assertFalse(
            $this->trait->hasObjectReference($class, $name)
        );
    }

    public function testGetObjectReference(): void
    {
        $this->referenceRepository
            ->expects(static::once())
            ->method('getReference')
            ->with(
                \sprintf(
                    '%s.%s',
                    $class = uniqid('class-'),
                    $name = uniqid('name-')
                )
            )
            ->willReturn($object = new \stdClass())
        ;

        static::assertSame(
            $object,
            $this->trait->getObjectReference($class, $name)
        );
    }

    public function testSetObjectReference(): void
    {
        $this->referenceRepository
            ->expects(static::once())
            ->method('setReference')
            ->with(
                \sprintf(
                    '%s.%s',
                    $class = uniqid('class-'),
                    $name = uniqid('name-')
                ),
                $object = new \stdClass()
            )
        ;

        $this->trait->setObjectReference($class, $name, $object);
    }
}
