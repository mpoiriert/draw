<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Entity;

interface MessageHolderInterface
{
    public function getOnHoldMessages(bool $clear): array;
}
