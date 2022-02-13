<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Entity;

trait MessageHolderTrait
{
    protected $onHoldMessages = [];

    public function getOnHoldMessages(bool $clear): array
    {
        $result = [];

        array_walk_recursive($this->onHoldMessages, function ($event) use (&$result) {
            $result[] = $event;
        });

        if ($clear) {
            $this->onHoldMessages = [];
        }

        return $result;
    }

    public static function useMessageHolderTrait($objectOrClass): bool
    {
        $class = $objectOrClass;

        do {
            $traits = class_uses($class, true);
            if (isset($traits[MessageHolderTrait::class])) {
                return true;
            }
        } while ($class = get_parent_class($class));

        return false;
    }
}
