<?php

namespace Draw\Bundle\DoctrineBusMessageBundle;

use SplQueue;

trait MessageHolderTrait
{
    private $messageQueue;

    public function messageQueue(): SplQueue
    {
        return $this->messageQueue ?: $this->messageQueue = new SplQueue();
    }
}
