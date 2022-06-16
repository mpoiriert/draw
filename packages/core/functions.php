<?php

namespace Draw\Component\Core;

if (!\function_exists(__NAMESPACE__.'\use_trait')) {
    function use_trait($objectOrClass, string $trait): bool
    {
        $class = $objectOrClass;

        do {
            $traits = class_uses($class, true);
            if (isset($traits[$trait])) {
                return true;
            }
        } while ($class = get_parent_class($class));

        return false;
    }
}
