<?php

namespace Draw\Component\Mailer\Twig;

use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranslationExtension extends AbstractExtension
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', [$this, 'trans']),
        ];
    }

    public function trans(string|\Stringable|array|TranslatableInterface|null $messages, string|array $arguments = [], ?string $domain = null, ?string $locale = null, ?int $count = null): ?string
    {
        if (!\is_array($messages)) {
            $messages = [$messages];
        }

        if (null !== $count) {
            $arguments['%count%'] = $count;
        }

        $result = reset($messages);
        foreach ($messages as $message) {
            if ($message instanceof TranslatableInterface) {
                if ($message instanceof TranslatableMessage && '' === $message->getMessage()) {
                    return '';
                }

                $result = $message->trans($this->translator, $locale ?? (\is_string($arguments) ? $arguments : null));

                if ($result != $message) {
                    return $result;
                }

                continue;
            }

            $result = $this->translator->trans($message, $arguments, $domain, $locale);
            if ($result !== $message) {
                return $result;
            }
        }

        return $result;
    }
}
