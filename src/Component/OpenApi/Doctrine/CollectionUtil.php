<?php

namespace Draw\Component\OpenApi\Doctrine;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Inflector\Inflector;

class CollectionUtil
{
    /**
     * @param $collectionOwner
     * @param array|Collection $newCollection
     */
    public static function replace(
        $collectionOwner,
        string $propertyName,
        $newCollection,
        callable $add = null,
        callable $remove = null
    ) {
        if (!$newCollection instanceof Collection) {
            $newCollection = new ArrayCollection($newCollection);
        }

        $currentCollection = call_user_func([$collectionOwner, 'get'.$propertyName]);

        if (null === $add) {
            $add = self::createMutatorMethod('add', $collectionOwner, $propertyName);
            if (null === $add) {
                $add = function ($collectionItem) use ($currentCollection) {
                    $currentCollection->add($collectionItem);
                };
            }
        }

        if (null === $remove) {
            $remove = self::createMutatorMethod('remove', $collectionOwner, $propertyName);
            if (null === $remove) {
                $remove = function ($collectionItem) use ($currentCollection) {
                    $currentCollection->removeElement($collectionItem);
                };
            }
        }

        foreach ($currentCollection as $element) {
            if (!$newCollection->contains($element)) {
                call_user_func($remove, $element);
            }
        }

        foreach ($newCollection as $element) {
            if (!$currentCollection->contains($element)) {
                call_user_func($add, $element);
            }
        }
    }

    private static function createMutatorMethod($methodPrefix, $collectionOwner, $propertyName): ?callable
    {
        $methodName = $methodPrefix.Inflector::singularize($propertyName);
        if (!method_exists($collectionOwner, $methodName)) {
            return null;
        }

        return function ($collectionItem) use ($collectionOwner, $methodName) {
            call_user_func([$collectionOwner, $methodName], $collectionItem);
        };
    }

    public static function assignPosition($element, Collection $collection, $attribute = 'position')
    {
        $method = 'get'.$attribute;
        $currentPosition = call_user_func([$element, $method]);
        if (null !== $currentPosition) {
            return;
        }

        $position = count($collection);
        if ($last = $collection->last()) {
            $position = max(call_user_func([$last, $method]) + 1, $position);
        }

        call_user_func([$element, 'set'.$attribute], $position);
    }
}
