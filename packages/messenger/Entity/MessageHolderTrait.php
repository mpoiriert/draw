<?php

namespace Draw\Component\Messenger\Entity;

trait MessageHolderTrait
{
    protected array $onHoldMessages = [];

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
}
