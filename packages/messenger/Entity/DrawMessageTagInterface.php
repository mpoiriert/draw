<?php

namespace Draw\Component\Messenger\Entity;

interface DrawMessageTagInterface
{
    public function getMessage(): ?DrawMessageInterface;

    public function getName(): ?string;
}
