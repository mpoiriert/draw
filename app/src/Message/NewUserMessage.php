<?php

namespace App\Message;

use App\Entity\User;
use Draw\Component\Messenger\AutoStamp\Message\StampingAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Draw\Component\Messenger\Message\AsyncMessageInterface;
use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use Symfony\Component\Messenger\Envelope;

class NewUserMessage implements DoctrineReferenceAwareInterface, AsyncMessageInterface, StampingAwareInterface
{
    private ?User $user = null;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getPropertiesWithDoctrineObject(): array
    {
        return ['user'];
    }

    public function stamp(Envelope $envelope): Envelope
    {
        return $envelope->with(new SearchableTagStamp([$this->user->getEmail()]));
    }
}
