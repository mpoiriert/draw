<?php

namespace Draw\Bundle\SonataExtraBundle\PreventDelete;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class PreventDelete
{
    public function __construct(
        private ?string $class = null,
        private ?string $relatedClass = null,
        private ?string $path = null,
        private bool $preventDelete = true
    ) {
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getRelatedClass(): ?string
    {
        return $this->relatedClass;
    }

    public function setRelatedClass(?string $relatedClass): static
    {
        $this->relatedClass = $relatedClass;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getPreventDelete(): bool
    {
        return $this->preventDelete;
    }

    public function setPreventDelete(bool $preventDelete): static
    {
        $this->preventDelete = $preventDelete;

        return $this;
    }
}
