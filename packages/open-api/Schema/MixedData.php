<?php

namespace Draw\Component\OpenApi\Schema;

final class MixedData
{
    public function __construct(public mixed $data)
    {
    }

    public static function convert(mixed $value, bool $valueIsArray = false): array|self|null
    {
        if (null === $value) {
            return null;
        }

        if ($valueIsArray && \is_array($value)) {
            foreach ($value as $key => $data) {
                $value[$key] = self::convert($data);
            }

            return $value;
        }

        if ($value instanceof self) {
            return $value;
        }

        return new self($value);
    }
}
