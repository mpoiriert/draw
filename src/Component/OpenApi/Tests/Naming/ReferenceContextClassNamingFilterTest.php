<?php

namespace Draw\Component\OpenApi\Tests\Naming;

use Draw\Component\OpenApi\Naming\ClassNamingFilterInterface;
use Draw\Component\OpenApi\Naming\ReferenceContextClassNamingFilter;
use PHPUnit\Framework\TestCase;

class ReferenceContextClassNamingFilterTest extends TestCase
{
    private $referenceContextClassNamingFilter;

    public function setUp()
    {
        $this->referenceContextClassNamingFilter = new ReferenceContextClassNamingFilter();
    }

    public function testConstruct()
    {
        $this->assertInstanceOf(ClassNamingFilterInterface::class, $this->referenceContextClassNamingFilter);
    }

    public function provideTestFilterClassName()
    {
        yield 'NoGroup' => [
            self::class,
            [],
            self::class,
            self::class,
        ];

        yield 'Group-Reference' => [
            self::class,
            ['serializer-groups' => ['reference']],
            self::class,
            self::class.'Reference',
        ];

        yield 'MultipleGroups' => [
            self::class,
            ['serializer-groups' => ['reference', 'other']],
            self::class,
            self::class,
        ];

        yield 'MultipleGroups' => [
            self::class,
            ['serializer-groups' => ['reference', 'other']],
            self::class,
            self::class,
        ];

        yield 'NewName' => [
            self::class,
            ['serializer-groups' => ['reference']],
            'NewClassName',
            'NewClassNameReference',
        ];
    }

    /**
     * @dataProvider provideTestFilterClassName
     */
    public function testFilterClassName(
        string $originalClassName,
        array $context,
        string $newName,
        string $expectedResult
    ) {
        $this->assertSame(
            $expectedResult,
            $this->referenceContextClassNamingFilter->filterClassName($originalClassName, $context, $newName)
        );
    }
}
