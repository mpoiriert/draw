<?php

namespace Draw\Component\Mailer\EventListener;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Mailer\Event\MessageEvent;
use Symfony\Component\Mime\Header\UnstructuredHeader;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;

class EmailWriterListener implements EventSubscriberInterface
{
    private ContainerInterface $serviceLocator;

    private array $writers = [];

    private array $sortedWriters = [];

    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::class => ['composeMessage', 200],
        ];
    }

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    public function registerEmailWriter(EmailWriterInterface $emailWriter): void
    {
        $class = \get_class($emailWriter);
        $forEmails = ReflectionAccessor::callMethod($emailWriter, 'getForEmails');
        foreach ($forEmails as $methodName => $priority) {
            if (\is_int($methodName)) {
                $methodName = $priority;
                $priority = 0;
            }

            $emailType = (new \ReflectionMethod($class, $methodName))->getParameters()[0]->getClass()->name;
            $this->addWriter($emailType, $emailWriter, $methodName, $priority);
        }
    }

    /**
     * @param EmailWriterInterface|string $writer
     */
    public function addWriter(string $emailClass, $writer, string $writerMethod, int $priority = 0): void
    {
        $this->writers[$emailClass][$priority][] = [$writer, $writerMethod];
        unset($this->sortedWriters[$emailClass]);
    }

    /**
     * @internal
     *
     * @return array<array{0: string|EmailWriterInterface, 1: string}>
     */
    public function getWriters(string $email): array
    {
        if (empty($this->writers[$email])) {
            return [];
        }

        if (!isset($this->sortedWriters[$email])) {
            $this->sortWriters($email);
        }

        return $this->sortedWriters[$email];
    }

    private function sortWriters(string $email): void
    {
        krsort($this->writers[$email]);
        $this->sortedWriters[$email] = array_merge(...$this->writers[$email]);
    }

    public function composeMessage(MessageEvent $event): void
    {
        $message = $event->getMessage();
        if (!$message instanceof Message) {
            return;
        }

        $headers = $message->getHeaders();
        if ($headers->has('X-DrawEmail')) {
            return;
        }

        $headers->add(new UnstructuredHeader('X-DrawEmail', '1'));

        $envelope = $event->getEnvelope();

        $types = $this->getTypes($message);

        foreach ($types as $type) {
            foreach ($this->getWriters($type) as $writerConfiguration) {
                [$writer, $writerMethod] = $writerConfiguration;
                $writer = $writer instanceof EmailWriterInterface ? $writer : $this->serviceLocator->get($writer);
                \call_user_func([$writer, $writerMethod], $message, $envelope);
            }
        }
    }

    private function getTypes(RawMessage $message): array
    {
        /* @phpstan-ignore-next-line */
        return [\get_class($message)]
            + class_parents($message)
            + class_implements($message);
    }
}
