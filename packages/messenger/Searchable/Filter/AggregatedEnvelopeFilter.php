<?php

namespace Draw\Component\Messenger\Searchable\Filter;

use Draw\Contracts\Messenger\EnvelopeFilterInterface;
use Symfony\Component\Messenger\Envelope;

class AggregatedEnvelopeFilter implements EnvelopeFilterInterface
{
    /**
     * @var array|EnvelopeFilterInterface[]
     */
    private array $filters = [];

    /**
     * @param array|EnvelopeFilterInterface[] $filters
     */
    public function __construct(array $filters = [])
    {
        foreach ($filters as $filter) {
            $this->addFilter($filter);
        }
    }

    public function addFilter(EnvelopeFilterInterface $filter): self
    {
        $this->filters[] = $filter;

        return $this;
    }

    public function __invoke(Envelope $envelope): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter($envelope)) {
                return false;
            }
        }

        return true;
    }
}
