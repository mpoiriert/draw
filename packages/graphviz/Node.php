<?php

namespace Draw\Component\Graphviz;

class Node implements \Stringable
{
    use AttributeHolderTrait;

    public function __construct(
        private string $name,
        array|AttributeBag $attributes = [],
    ) {
        $this->initializeAttributeBag($attributes);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        $result = $this->name;

        if ($attributes = (string) $this->attributes) {
            $result .= ' '.$attributes;
        }

        return $result.';';
    }
}
