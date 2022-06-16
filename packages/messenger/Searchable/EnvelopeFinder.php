<?php

namespace Draw\Component\Messenger\Searchable;

use Draw\Component\Messenger\Expirable\Stamp\ExpirationStamp;
use Draw\Component\Messenger\Searchable\Filter\AggregatedEnvelopeFilter;
use Draw\Component\Messenger\Searchable\Stamp\FoundFromTransportStamp;
use Draw\Contracts\Messenger\EnvelopeFilterInterface;
use Draw\Contracts\Messenger\EnvelopeFinderInterface;
use Draw\Contracts\Messenger\Exception\MessageNotFoundException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Transport\Receiver\ListableReceiverInterface;

class EnvelopeFinder implements EnvelopeFinderInterface
{
    private TransportRepository $transportRepository;

    private EnvelopeFilterInterface $defaultFilter;

    public function __construct(TransportRepository $transportRepository)
    {
        $this->transportRepository = $transportRepository;
        $this->defaultFilter = new AggregatedEnvelopeFilter([ExpirationStamp::createEnvelopeFilter()]);
    }

    public function findById(string $messageId, EnvelopeFilterInterface $filter = null): Envelope
    {
        if ($filter) {
            $filter = (clone $this->defaultFilter)->addFilter($filter);
        } else {
            $filter = $this->defaultFilter;
        }

        foreach ($this->transportRepository->findAll() as $transportName => $transport) {
            if (!$transport instanceof ListableReceiverInterface) {
                continue;
            }

            if ($envelope = $transport->find($messageId)) {
                $envelope = $envelope->with(new FoundFromTransportStamp($transportName));
                if (\call_user_func($filter, $envelope)) {
                    return $envelope;
                }
            }
        }

        throw new MessageNotFoundException($messageId);
    }

    /**
     * Return all envelop that match all tags.
     *
     * @return array|Envelope[]
     */
    public function findByTags(array $tags): array
    {
        $envelopes = [];
        foreach ($this->transportRepository->findAll() as $transportName => $transport) {
            if (!$transport instanceof SearchableTransportInterface) {
                continue;
            }

            foreach ($transport->findByTags($tags) as $envelope) {
                $envelopes[] = $envelope->with(new FoundFromTransportStamp($transportName));
            }
        }

        return $envelopes;
    }
}
