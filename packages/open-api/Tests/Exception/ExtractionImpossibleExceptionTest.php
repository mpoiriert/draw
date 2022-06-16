<?php

namespace Draw\Component\OpenApi\Tests\Exception;

use Draw\Component\OpenApi\Exception\ExtractionImpossibleException;
use Exception;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Draw\Component\OpenApi\Exception\ExtractionImpossibleException
 */
class ExtractionImpossibleExceptionTest extends TestCase
{
    private ExtractionImpossibleException $object;

    protected function setUp(): void
    {
        $this->object = new ExtractionImpossibleException();
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            Exception::class,
            $this->object
        );
    }
}
