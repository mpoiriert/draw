<?php

namespace Draw\Bundle\TesterBundle\Messenger;

use Draw\Component\Core\FilterExpression\Evaluator;
use Draw\Component\Core\FilterExpression\Query;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;

class TransportTester
{
    /**
     * @var InMemoryTransport
     */
    private $transport;

    /**
     * @var Evaluator
     */
    private $evaluator;

    public function __construct(InMemoryTransport $transport, Evaluator $evaluator)
    {
        $this->evaluator = $evaluator;
        $this->transport = $transport;
    }

    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    public function assertMessageMatch(Query $query, $count = 1, $message = ''): void
    {
        $messages = [];
        foreach ($this->transport->get() as $envelope) {
            $messages[] = $envelope->getMessage();
        }

        $messages = $this->evaluator->execute($query, $messages);

        TestCase::assertCount(
            $count,
            $messages,
            $message
        );
    }

    public function reset()
    {
        $this->transport->reset();
    }
}