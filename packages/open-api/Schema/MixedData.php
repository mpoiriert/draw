<?php

namespace Draw\Component\OpenApi\Schema;

final class MixedData
{
    /**
     * @var mixed
     */
    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public static function convert($value, $valueIsArray = false)
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
