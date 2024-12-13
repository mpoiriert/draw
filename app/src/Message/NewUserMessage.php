<?php

namespace App\Message;

use App\Entity\User;
use Draw\Component\Messenger\AutoStamp\Message\StampingAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Exception\ObjectNotFoundException;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Message\DoctrineReferenceAwareInterface;
use Draw\Component\Messenger\DoctrineEnvelopeEntityReference\Stamp\PropertyReferenceStamp;
use Draw\Component\Messenger\Message\AsyncLowPriorityMessageInterface;
use Draw\Component\Messenger\Searchable\Stamp\SearchableTagStamp;
use Symfony\Component\Messenger\Envelope;

class NewUserMessage implements DoctrineReferenceAwareInterface, AsyncLowPriorityMessageInterface, StampingAwareInterface
{
    private PropertyReferenceStamp|User|null $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getUser(): User
    {
        if ($this->user instanceof PropertyReferenceStamp) {
            throw new ObjectNotFoundException(User::class, $this->user);
        }

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
