<?php

namespace Draw\Component\Messenger\Entity;

interface MessageHolderInterface
{
    public function getOnHoldMessages(bool $clear): array;
}
