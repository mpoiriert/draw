<?php

namespace App\Document;

use App\Message\NewTestDocumentMessage;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderInterface;
use Draw\Component\Messenger\DoctrineMessageBusHook\Model\MessageHolderTrait;

#[ODM\Document]
#[ODM\HasLifecycleCallbacks]
class TestDocument implements MessageHolderInterface
{
    use MessageHolderTrait;

    #[ODM\Id]
    public string $id;

    #[ODM\PrePersist]
    public function raiseNewEvent(): void
    {
        $this->onHoldMessages[] = new NewTestDocumentMessage();
    }
}
