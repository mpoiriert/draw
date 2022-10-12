<?php

namespace Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message;

interface DoctrineReferenceAwareInterface
{
    /**
     * @return array<string>
     */
    public function getPropertiesWithDoctrineObject(): array;
}
