<?php

namespace Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Exception;

use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Stamp\PropertyReferenceStamp;
use Symfony\Component\Messenger\Exception\UnrecoverableExceptionInterface;

class ObjectNotFoundException extends \Exception implements UnrecoverableExceptionInterface
{
    public function __construct(
        private string $objectClass,
        private ?PropertyReferenceStamp $propertyReferenceStamp = null,
    ) {
        $message = \sprintf(
            'Object of class [%s] not found.',
            $objectClass,
        );

        if ($propertyReferenceStamp) {
            $message .= \sprintf(
                ' Identifiers [%s]',
                json_encode($propertyReferenceStamp->getIdentifiers())
            );
        }

        parent::__construct($message);
    }

    public function getObjectClass(): string
    {
        return $this->objectClass;
    }

    public function getPropertyReferenceStamp(): ?PropertyReferenceStamp
    {
        return $this->propertyReferenceStamp;
    }
}
