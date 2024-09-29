<?php

namespace Draw\Component\Core\Tests;

use PHPUnit\Framework\TestCase;

use function Draw\Component\Core\use_trait;

/**
 * @internal
 */
class FunctionTest extends TestCase
{
    public function testUseTraitNotExistsTrait(): void
    {
        static::assertFalse(use_trait(\stdClass::class, 'Trait'));
    }

    public function testUseTraitDoesNotUseTrait(): void
    {
        static::assertFalse(use_trait(\stdClass::class, StubTrait::class));
    }

    public function testUseTrait(): void
    {
        static::assertTrue(
            use_trait(
                new class {
                    use StubTrait;
                },
                StubTrait::class
            )
        );
    }

    public function testUseTraitParentClass(): void
    {
        static::assertTrue(
            use_trait(
                new class extends StubClass {
                },
                StubTrait::class
            )
        );
    }
}
