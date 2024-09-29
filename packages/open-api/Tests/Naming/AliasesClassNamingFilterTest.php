<?php

namespace Draw\Component\OpenApi\Tests\Naming;

use Draw\Component\OpenApi\Naming\AliasesClassNamingFilter;
use Draw\Component\OpenApi\Naming\ClassNamingFilterInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversClass(AliasesClassNamingFilter::class)]
class AliasesClassNamingFilterTest extends TestCase
{
    private AliasesClassNamingFilter $object;

    private array $aliases;

    protected function setUp(): void
    {
        $this->object = new AliasesClassNamingFilter(
            $this->aliases = [
                'remove-namespace' => [
                    'class' => uniqid('namespace\\').'\\',
                    'alias' => '',
                ],
                'change-full-class-name' => [
                    'class' => uniqid('namespace\\'),
                    'alias' => uniqid('newNamespace\\'),
                ],
                'change-namespace' => [
                    'class' => uniqid('namespace\\').'\\',
                    'alias' => uniqid('newNamespace\\').'\\',
                ],
            ]
        );
    }

    public function testConstruct(): void
    {
        static::assertInstanceOf(
            ClassNamingFilterInterface::class,
            $this->object
        );
    }

    public function testFilterClassNameNoChange(): void
    {
        static::assertSame(
            $originalClassName = uniqid('class'),
            $this->object->filterClassName($originalClassName, [], null)
        );
    }

    public function testFilterClassNameNoChangeNewNoChange(): void
    {
        static::assertSame(
            $newClassName = uniqid('class'),
            $this->object->filterClassName(uniqid('class'), [], $newClassName)
        );
    }

    public function testFilterClassNameRemoveNamespace(): void
    {
        static::assertSame(
            $className = uniqid('class'),
            $this->object->filterClassName($this->aliases['remove-namespace']['class'].$className)
        );
    }

    public function testFilterClassNameChangeNamespace(): void
    {
        static::assertSame(
            $this->aliases['change-namespace']['alias'].($className = uniqid('class')),
            $this->object->filterClassName($this->aliases['change-namespace']['class'].$className)
        );
    }

    public function testFilterClassNameChangeClass(): void
    {
        static::assertSame(
            $this->aliases['change-full-class-name']['alias'],
            $this->object->filterClassName($this->aliases['change-full-class-name']['class'])
        );
    }
}
