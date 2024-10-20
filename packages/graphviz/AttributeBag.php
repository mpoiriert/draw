<?php

namespace Draw\Component\Graphviz;

class AttributeBag
{
    public function __construct(
        private array $attributes = [],
    ) {
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function __toString(): string
    {
        if (empty($this->attributes)) {
            return '';
        }

        $attributes = [];

        foreach ($this->attributes as $key => $value) {
            $attributes[] = \sprintf(
                '%s=%s',
                $key,
                $this->formatAttribute($value)
            );
        }

        return \sprintf("[\n    %s\n  ]",
            implode(",\n    ", $attributes)
        );
    }

    private function formatAttribute(mixed $value)
    {
        if (\is_string($value)) {
            if (str_starts_with($value, '<')) {
                return $value;
            }

            return '"'.addslashes($value).'"'; // Quote and escape
        }

        if (\is_bool($value)) {
            return $value ? 'true' : 'false'; // Convert to string
        }

        return $value; // Return as-is for non-strings
    }
}
