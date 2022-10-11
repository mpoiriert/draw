<?php

namespace Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message;

interface DoctrineReferenceAwareInterface
{
    /**
     * @return array<mixed,object>
     */
    public function getDoctrineObjects(): array;

    /**
     * @param array<mixed,object> $objects
     */
    public function restoreDoctrineObjects(array $objects): void;
}
