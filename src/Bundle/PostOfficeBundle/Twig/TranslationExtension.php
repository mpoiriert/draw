<?php

namespace Draw\Bundle\PostOfficeBundle\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TranslationExtension extends AbstractExtension
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters()
    {
        return [
            new TwigFilter('trans', [$this, 'trans']),
        ];
    }

    public function trans($messages, array $arguments = [], $domain = null, $locale = null, $count = null)
    {
        if (!is_array($messages)) {
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
