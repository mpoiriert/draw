<?php

namespace Draw\Bundle\SonataIntegrationBundle\Messenger\EventListener;

use Draw\Bundle\SonataExtraBundle\Block\Event\FinalizeContextEvent;
use Draw\Contracts\Messenger\TransportRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;

class FinalizeContextQueueCountEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FinalizeContextEvent::class => 'finalizeContext',
        ];
    }

    public function __construct(private TransportRepositoryInterface $transportRepository)
    {
    }

    public function finalizeContext(FinalizeContextEvent $event): void
    {
        $blockContext = $event->getBlockContext();

        $transportName = $blockContext->getSetting('extra_data')['transport_name'] ?? null;

        if (!$transportName) {
            return;
        }

        $event->stopPropagation();

        $transport = $this->transportRepository->get($transportName);

        if ($transport instanceof MessageCountAwareInterface) {
            $blockContext->setSetting('count', $transport->getMessageCount());
        }
    }
}
