<?php

namespace Draw\Component\Core;

if (!\function_exists(__NAMESPACE__.'\use_trait')) {
    function use_trait($objectOrClass, string $trait): bool
    {
        $class = $objectOrClass;

        $allTraits = [];
        do {
            $traits = class_uses($class);
            if (isset($traits[$trait])) {
                return true;
            }

            $allTraits = array_merge($allTraits, $traits);
        } while ($class = get_parent_class($class));

        return array_any(
            array_unique($allTraits),
            static fn ($usedTrait): bool => use_trait($usedTrait, $trait)
        );
    }
}
