<?php

namespace Draw\Component\Graphviz;

class Edge
{
    use AttributeHolderTrait;

    public function __construct(
        private string $from,
        private string $to,
        array|AttributeBag $attributes = [],
        private bool $directed = true,
    ) {
        $this->initializeAttributeBag($attributes);
    }

    public function getDirected(): bool
    {
        return $this->directed;
    }

    public function setDirected(bool $directed): self
    {
        $this->directed = $directed;

        return $this;
    }

    public function getFrom(): string
    {
        return $this->from;
    }

    public function getTo(): string
    {
        return $this->to;
    }

    public function __toString(): string
    {
        $result = \sprintf(
            '%s %s %s',
            $this->from,
            $this->directed ? '->' : '--',
            $this->to
        );

        if ($attributes = (string) $this->attributes) {
            $result .= ' '.$attributes;
        }

        return $result.';';
    }
}
