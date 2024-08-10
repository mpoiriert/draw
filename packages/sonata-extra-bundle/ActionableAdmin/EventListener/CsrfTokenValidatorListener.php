<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\EventListener;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\PreExecutionEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\PrepareExecutionEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

class CsrfTokenValidatorListener
{
    public const INTENTION = 'csrf_token.intention';

    public const TOKEN = 'csrf_token.token';

    public function __construct(
        private ?CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    #[AsEventListener]
    public function onPrepareExecutionEvent(PrepareExecutionEvent $event): void
    {
        $objectActionExecutioner = $event->getObjectActionExecutioner();

        $tokenIntention = $this->getTokenIntention($objectActionExecutioner->options);

        if (!$tokenIntention) {
            return;
        }

        if (!\array_key_exists(self::TOKEN, $objectActionExecutioner->options)) {
            $objectActionExecutioner->options[self::TOKEN] = $this->csrfTokenManager->getToken($tokenIntention)->getValue();
        }
    }

    #[AsEventListener]
    public function onPreExecutionEvent(PreExecutionEvent $event): void
    {
        $objectActionExecutioner = $event->getObjectActionExecutioner();

        $tokenIntention = $this->getTokenIntention($objectActionExecutioner->options);

        if (!$tokenIntention) {
            return;
        }

        $token = $objectActionExecutioner->options[self::TOKEN] ?? null;

        if ($this->csrfTokenManager->isTokenValid(new CsrfToken($tokenIntention, $token))) {
            return;
        }

        throw new \RuntimeException('The csrf token is not valid, CSRF attack?');
    }

    private function getTokenIntention(array $options): ?string
    {
        if (!$this->csrfTokenManager) {
            return null;
        }

        return $options[self::INTENTION] ?? null;
    }
}
