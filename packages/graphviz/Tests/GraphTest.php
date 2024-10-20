<?php

namespace Draw\Component\Graphviz\Tests;

use Draw\Component\Graphviz\Edge;
use Draw\Component\Graphviz\Graph;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
class GraphTest extends TestCase
{
    public function testGraphDirectional(): void
    {
        $graph = (new Graph('test'))
            ->addEdge(
                new Edge('a', 'b', directed: true)
            )
        ;

        static::assertSame(<<<'TEXT'
            digraph test {
              a -> b;
            }
            TEXT,
            (string) $graph);
    }

    public function testGraphUnDirectional(): void
    {
        $graph = new Graph('test');

        static::assertSame(
            'graph test {}',
            (string) $graph
        );
    }

    public function testGraphWithAttribute(): void
    {
        $graph = new Graph('test', ['rankdir' => 'LR', 'bgcolor' => 'red']);

        static::assertSame(<<<'TEXT'
            graph test {
              graph [
                rankdir="LR",
                bgcolor="red"
              ];
            }
            TEXT,
            (string) $graph
        );
    }
}
