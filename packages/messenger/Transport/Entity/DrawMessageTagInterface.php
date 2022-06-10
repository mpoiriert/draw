<?php

namespace Draw\Component\Messenger\Transport\Entity;

interface DrawMessageTagInterface
{
    public function getMessage(): ?DrawMessageInterface;

    public function getName(): ?string;
}
