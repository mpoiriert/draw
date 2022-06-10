<?php

namespace Draw\Component\Messenger\DoctrineMessageBusHook\Entity;

interface MessageHolderInterface
{
    public function getOnHoldMessages(bool $clear): array;
}
