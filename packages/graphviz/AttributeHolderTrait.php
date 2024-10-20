<?php

namespace Draw\Component\Graphviz;

trait AttributeHolderTrait
{
    private AttributeBag $attributes;

    private function initializeAttributeBag(
        array|AttributeBag $attributes,
    ): void {
        $this->attributes = $attributes instanceof AttributeBag
            ? $attributes
            : new AttributeBag($attributes);
    }

    public function getAttributes(): AttributeBag
    {
        return $this->attributes;
    }
}
