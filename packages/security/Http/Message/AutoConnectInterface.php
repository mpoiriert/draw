<?php

namespace Draw\Component\Security\Http\Message;

interface AutoConnectInterface
{
    public function getUserIdentifier(): string;
}
