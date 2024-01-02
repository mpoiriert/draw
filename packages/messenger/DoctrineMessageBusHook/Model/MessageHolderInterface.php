<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\Model;

interface MessageHolderInterface
{
    public function getOnHoldMessages(bool $clear): array;
}
