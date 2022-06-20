<?php

namespace Draw\DoctrineExtra\Common\Collections;

use Doctrine\Common\Collections\Collection;

class CollectionUtil
{
    public static function assignPosition($element, Collection $collection, $attribute = 'position'): void
    {
        $method = 'get'.$attribute;
        $currentPosition = \call_user_func([$element, $method]);
        if (null !== $currentPosition) {
            return;
        }

        $position = \count($collection);
        if ($last = $collection->last()) {
            $position = max(\call_user_func([$last, $method]) + 1, $position);
        }

        \call_user_func([$element, 'set'.$attribute], $position);
    }
}
