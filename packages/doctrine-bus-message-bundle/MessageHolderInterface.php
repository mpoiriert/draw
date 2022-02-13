<?php

namespace Draw\Bundle\DoctrineBusMessageBundle;

use SplQueue;

/**
 * @deprecated
 */
interface MessageHolderInterface
{
    public function messageQueue(): SplQueue;
}
