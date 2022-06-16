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

    private int $delay;

    private Security $security;

    private UrlGeneratorInterface $urlGenerator;

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

    public function __construct(Security $security, UrlGeneratorInterface $urlGenerator, int $delay = 3600)
    {
        $this->delay = $delay;
        $this->security = $security;
        $this->urlGenerator = $urlGenerator;
    }

    public function onKernelRequestInvalidate(RequestEvent $event): void
    {
        switch (true) {
            case !$event->isMainRequest():
            case null === $request = $event->getRequest():
            case !$request->hasSession():
                return;
        }

        $session = $request->getSession();

        $lastUsed = $session->get(static::LAST_USED_SESSION_ATTRIBUTE);

        if (null !== $lastUsed && $this->delay < (time() - $lastUsed)) {
            $session->invalidate();
        }
    }

    public function onKernelResponseSetLastUsed(ResponseEvent $event): void
    {
        switch (true) {
            case !$event->isMainRequest():
            case null === $request = $event->getRequest():
            case !$request->hasSession():
                return;
        }

        $request->getSession()->set(static::LAST_USED_SESSION_ATTRIBUTE, time());
    }

    public function onKernelResponseAddDialog(ResponseEvent $event): void
    {
        $response = $event->getResponse();

        switch (true) {
            case null === $this->security->getUser():
            case !$event->isMainRequest():
            case 0 !== strpos($response->headers->get('Content-type', ''), 'text/html'):
            case !\is_string($content = $response->getContent()):
            case false === strpos($content, '<meta data-sonata-admin'):
            case false === strpos($content, '<title>'):
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
