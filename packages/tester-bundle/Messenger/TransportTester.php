<?php

namespace Draw\Bundle\TesterBundle\Messenger;

use Draw\Component\Core\FilterExpression\Evaluator;
use Draw\Component\Core\FilterExpression\Expression\ConstraintExpression;
use Draw\Component\Core\FilterExpression\Expression\Expression;
use Draw\Component\Core\FilterExpression\Query;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Messenger\Transport\InMemoryTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;
use Symfony\Component\Validator\Constraints\Type;

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

    public function __construct(TransportInterface $transport, Evaluator $evaluator)
    {
        if (!$transport instanceof InMemoryTransport) {
            throw new RuntimeException(sprintf('TransportTester only support [%s]. Object of class [%s]', InMemoryTransport::class, get_class($transport)));
        }
        $this->evaluator = $evaluator;
        $this->transport = $transport;
    }

    public function getTransport(): TransportInterface
    {
        return $this->transport;
    }

    public function assertMessageMatch(
        string $messageClass,
        Expression $expression = null,
        $count = 1,
        $message = ''
    ): array {
        $messages = [];
        foreach ($this->transport->get() as $envelope) {
            $messages[] = $envelope->getMessage();
        }

        $query = (new Query())
            ->where(new ConstraintExpression(null, new Type($messageClass)));

        if ($expression) {
            $query = $query->andWhere($expression);
        }

        $messages = iterator_to_array($this->evaluator->execute($query, $messages));

        TestCase::assertCount(
            $count,
            $messages,
            $message
        );

        return $messages;
    }

    public function reset()
    {
        $this->transport->reset();
    }
}
