<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Message;

interface LifeCycleAwareMessageInterface
{
    public function preSend($messageHolder): void;
}
