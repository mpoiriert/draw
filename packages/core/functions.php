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

        foreach (array_unique($allTraits) as $usedTrait) {
            if (use_trait($usedTrait, $trait)) {
                return true;
            }
        }

        return false;
    }
}
