<?php

namespace Draw\Component\Mailer\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranslationExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters(): array
    {
        return [
            new TwigFilter('trans', [$this, 'trans']),
        ];
    }

    public function trans($messages, array $arguments = [], $domain = null, $locale = null, $count = null): ?string
    {
        if (!\is_array($messages)) {
            $messages = [$messages];
        }

        if (null !== $count) {
            $arguments['%count%'] = $count;
        }

        $result = reset($messages);
        foreach ($messages as $message) {
            $result = $this->translator->trans($message, $arguments, $domain, $locale);
            if ($result != $message) {
                return $result;
            }
        }

        return $result;
    }
}
