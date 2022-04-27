<?php

namespace Draw\Component\Core\Tests;

use function Draw\Component\Core\use_trait;
use PHPUnit\Framework\TestCase;
use stdClass;

class FunctionTest extends TestCase
{
    public function testUseTraitNotExistsTrait(): void
    {
        $this->assertFalse(use_trait(stdClass::class, 'Trait'));
    }

    public function testUseTraitDoesNotUseTrait(): void
    {
        $this->assertFalse(use_trait(stdClass::class, StubTrait::class));
    }

    public function testUseTrait(): void
    {
        $this->assertTrue(
            use_trait(
                new class() {
                    use StubTrait;
                },
                StubTrait::class
            )
        );
    }

    public function testUseTraitParentClass(): void
    {
        $this->assertTrue(
            use_trait(
                new class() extends StubClass {
                },
                StubTrait::class
            )
        );
    }
}
