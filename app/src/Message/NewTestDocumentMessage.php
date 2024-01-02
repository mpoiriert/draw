<?php

namespace App\Message;

use App\Document\TestDocument;
use Draw\Component\Messenger\AutoStamp\Message\StampingAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

class NewTestDocumentMessage implements DoctrineReferenceAwareInterface, StampingAwareInterface
{
    private ?TestDocument $testDocument;

    public function __construct(?TestDocument $testDocument = null)
    {
        $this->testDocument = $testDocument;
    }

    public function getTestDocument(): TestDocument
    {
        if (null === $this->testDocument) {
            throw new UnrecoverableMessageHandlingException('testDocument is null');
        }

        return $this->testDocument;
    }

    public function getPropertiesWithDoctrineObject(): array
    {
        return ['testDocument'];
    }

    public function stamp(Envelope $envelope): Envelope
    {
        return $envelope->with(new SearchableTagStamp([$this->testDocument->id]));
    }
}
