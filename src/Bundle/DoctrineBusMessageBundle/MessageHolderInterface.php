<?php

namespace Draw\Bundle\DoctrineBusMessageBundle;

use SplQueue;

interface MessageHolderInterface
{
    public function messageQueue(): SplQueue;
}