<?php

namespace Draw\Component\Mailer\BodyRenderer;

use Draw\Component\Mailer\Email\LocalizeEmailInterface;
use Symfony\Component\Mime\BodyRendererInterface;
use Symfony\Component\Mime\Message;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class LocalizeBodyRenderer implements BodyRendererInterface
{
    private ?LocaleAwareInterface $translator = null;

    public function __construct(
        private BodyRendererInterface $bodyRenderer,
        TranslatorInterface $translator
    ) {
        if ($translator instanceof LocaleAwareInterface) {
            $this->translator = $translator;
        }
    }

    public function render(Message $message): void
    {
        $currentLocale = null;
        if ($this->translator && $message instanceof LocalizeEmailInterface && $message->getLocale()) {
            $currentLocale = $this->translator->getLocale();
            $this->translator->setLocale($message->getLocale());
        }

        try {
            $this->bodyRenderer->render($message);
        } finally {
            if ($currentLocale) {
                $this->translator?->setLocale($currentLocale);
            }
        }
    }
}
