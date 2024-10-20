<?php

namespace Draw\Component\Graphviz;

class Graph implements \Stringable
{
    use AttributeHolderTrait;

    /**
     * @var array<Node>
     */
    private array $nodes = [];

    /**
     * @var array<Edge>
     */
    private array $edges = [];

    private bool $directed = false;

    public function __construct(
        private ?string $name = null,
        array|AttributeBag $attributes = [],
    ) {
        $this->initializeAttributeBag($attributes);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function addNode(Node $node): self
    {
        $this->nodes[$node->getName()] = $node;

        return $this;
    }

    public function getNode(string $name): ?Node
    {
        return $this->nodes[$name] ?? null;
    }

    public function removeNode(string $name): self
    {
        unset($this->nodes[$name]);

        return $this;
    }

    public function getNodes(): array
    {
        return $this->nodes;
    }

    public function addEdge(Edge $edge): self
    {
        $this->edges[] = $edge;

        return $this;
    }

    public function getEdges(): array
    {
        return $this->edges;
    }

    public function __toString(): string
    {
        foreach ($this->edges as $edge) {
            if ($edge->getDirected()) {
                $this->directed = true;
                break;
            }
        }

        $result = \sprintf(
            '%s %s {',
            $this->directed ? 'digraph' : 'graph',
            $this->name
        );

        if ($attributes = (string) $this->attributes) {
            $result .= "\n  graph ".$attributes.";\n";
        }

        if (!empty($this->nodes)) {
            $result .= "\n  ".implode("\n\n  ", $this->nodes)."\n";
        }

        if (!empty($this->edges)) {
            $result .= "\n  ".implode("\n\n  ", $this->edges)."\n";
        }

        $result .= '}';

        return $result;
    }
}
