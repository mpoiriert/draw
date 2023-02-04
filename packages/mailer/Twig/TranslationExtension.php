<?php

namespace Draw\Component\Mailer\Twig;

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

    /**
     * @param string|string[] $messages
     */
    public function trans(string|array $messages, array $arguments = [], ?string $domain = null, ?string $locale = null, ?int $count = null): ?string
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
