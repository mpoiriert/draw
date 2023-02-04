<?php

namespace Draw\Bundle\SonataExtraBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Security;

class SessionTimeoutRequestListener implements EventSubscriberInterface
{
    private const LAST_USED_SESSION_ATTRIBUTE = 'draw_sonata_integration_last_used';

    public static function getSubscribedEvents(): array
    {
        return [
            RequestEvent::class => [
                ['onKernelRequestInvalidate', 9],
            ],
            ResponseEvent::class => [
                ['onKernelResponseSetLastUsed', 0],
                ['onKernelResponseAddDialog', -2000],
            ],
        ];
    }

    public function __construct(
        private Security $security,
        private UrlGeneratorInterface $urlGenerator,
        private int $delay = 3600
    ) {
    }

    public function onKernelRequestInvalidate(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->hasSession()) {
            return;
        }

        $session = $request->getSession();

        $lastUsed = $session->get(self::LAST_USED_SESSION_ATTRIBUTE);

        if (null !== $lastUsed && $this->delay < (time() - $lastUsed)) {
            $session->invalidate();
        }
    }

    public function onKernelResponseSetLastUsed(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();

        if (!$request->hasSession()) {
            return;
        }

        $request->getSession()->set(self::LAST_USED_SESSION_ATTRIBUTE, time());
    }

    public function onKernelResponseAddDialog(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        if (!$this->security->getUser()) {
            return;
        }

        if (!$event->isMainRequest()) {
            return;
        }

        if (!str_starts_with($response->headers->get('Content-type', ''), 'text/html')) {
            return;
        }

        if (!\is_string($content = $response->getContent())) {
            return;
        }

        if (!str_contains($content, '<meta data-sonata-admin')) {
            return;
        }

        if (!str_contains($content, '<title>')) {
            return;
        }

        $content = str_replace(
            '<title>',
            sprintf(
                '
  <script type="text/javascript">
    const sessionHandler = new SessionExpirationHandler(%s,"%s","%s");
  </script>

  <title>',
                $this->delay,
                $this->urlGenerator->generate('keep_alive'),
                $this->urlGenerator->generate('admin_login')
            ),
            $content
        );

        $response->setContent($content);
    }
}
