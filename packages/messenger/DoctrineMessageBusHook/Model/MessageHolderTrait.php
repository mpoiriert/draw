<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\Model;

trait MessageHolderTrait
{
    protected array $onHoldMessages = [];

    /**
     * @return object[]
     */
    public function getOnHoldMessages(bool $clear): array
    {
        $result = [];

        array_walk_recursive($this->onHoldMessages, function ($event) use (&$result): void {
            $result[] = $event;
        });

        if ($clear) {
            $this->onHoldMessages = [];
        }

        return $result;
    }
}
