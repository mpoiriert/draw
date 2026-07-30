<?php

namespace Draw\Component\Messenger\Searchable\Filter;

use Draw\Contracts\Messenger\EnvelopeFilterInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\SentToFailureTransportStamp;

final class MustNotBeStampedEnvelopeFilter implements EnvelopeFilterInterface
{
    /**
     * @param string[] $stampClasses
     */
    public function __construct(private array $stampClasses)
    {
    }

    public function __invoke(Envelope $envelope): bool
    {
        return array_all(
            $this->stampClasses,
            static fn (string $stamp): bool => !$envelope->last($stamp)
        );
    }

    public static function sentToFailureTransport(): self
    {
        return new self([SentToFailureTransportStamp::class]);
    }
}
