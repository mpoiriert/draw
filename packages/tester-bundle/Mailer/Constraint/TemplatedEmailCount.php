<?php

namespace Draw\Bundle\TesterBundle\Mailer\Constraint;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Component\Mailer\Event\MessageEvents;
use Symfony\Component\Mime\Message;

final class TemplatedEmailCount extends Constraint
{
    public function __construct(
        private int $expectedValue,
        private string $type,
        private ?string $template = null,
        private ?string $transport = null
    ) {
    }

    public function toString(): string
    {
        return sprintf(
            '%shas "%d" %s templated emails %s',
            $this->transport ? $this->transport.' ' : '',
            $this->expectedValue,
            $this->type,
            $this->template ? 'with template "'.$this->template.'"' : '',
        );
    }

    /**
     * @param MessageEvents $other
     */
    protected function matches($other): bool
    {
        return $this->expectedValue === $this->countEmails($other);
    }

    /**
     * @param MessageEvents $other
     */
    protected function failureDescription($other): string
    {
        return sprintf('the Transport %s (%d)', $this->toString(), $this->countEmails($other));
    }

    private function countEmails(MessageEvents $events): int
    {
        $count = 0;
        foreach ($events->getEvents($this->transport) as $event) {
            $message = $event->getMessage();
            if (!$message instanceof Message) {
                continue;
            }

            if (!$message->getHeaders()->has('X-DrawEmail-'.$this->type.'Template')) {
                continue;
            }

            if (
                $this->template
                && $message->getHeaders()->get('X-DrawEmail-'.$this->type.'Template')->getBodyAsString() !== $this->template) {
                continue;
            }

            ++$count;
        }

        return $count;
    }
}
