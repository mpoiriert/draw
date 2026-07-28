<?php

namespace Draw\Component\Mailer;

use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Component\Core\Reflection\ReflectionExtractor;
use Draw\Component\Mailer\Email\LocalizeEmailInterface;
use Draw\Component\Mailer\EmailWriter\EmailWriterInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mime\Message;
use Symfony\Component\Mime\RawMessage;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class EmailComposer
{
    private array $writers = [];

    private array $sortedWriters = [];

    private ?LocaleAwareInterface $translator = null;

    public function __construct(
        private ContainerInterface $serviceLocator,
        TranslatorInterface $translator,
    ) {
        if ($translator instanceof LocaleAwareInterface) {
            $this->translator = $translator;
        }
    }

    public function compose(Message $message, Envelope $envelope): void
    {
        $currentLocale = null;

        if ($this->translator && $message instanceof LocalizeEmailInterface && $message->getLocale()) {
            $currentLocale = $this->translator->getLocale();
            $this->translator->setLocale($message->getLocale());
        }

        try {
            foreach ($this->getTypes($message) as $type) {
                foreach ($this->getWriters($type) as $writerConfiguration) {
                    [$writer, $writerMethod] = $writerConfiguration;
                    $writer = $writer instanceof EmailWriterInterface ? $writer : $this->serviceLocator->get($writer);
                    \call_user_func([$writer, $writerMethod], $message, $envelope);
                }
            }
        } finally {
            if ($currentLocale) {
                $this->translator?->setLocale($currentLocale);
            }
        }
    }

    public function registerEmailWriter(EmailWriterInterface $emailWriter): void
    {
        $class = $emailWriter::class;
        $forEmails = ReflectionAccessor::callMethod($emailWriter, 'getForEmails');
        foreach ($forEmails as $methodName => $priority) {
            if (\is_int($methodName)) {
                $methodName = $priority;
                $priority = 0;
            }

            $emailTypes = ReflectionExtractor::getClasses(
                (new \ReflectionMethod($class, $methodName))->getParameters()[0]->getType()
            );

            foreach ($emailTypes as $emailType) {
                $this->addWriter($emailType, $emailWriter, $methodName, $priority);
            }
        }
    }

    public function addWriter(
        string $emailClass,
        EmailWriterInterface|string $writer,
        string $writerMethod,
        int $priority = 0,
    ): void {
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

    private function getTypes(RawMessage $message): array
    {
        return [$message::class]
            + class_parents($message)
            + class_implements($message);
    }
}
