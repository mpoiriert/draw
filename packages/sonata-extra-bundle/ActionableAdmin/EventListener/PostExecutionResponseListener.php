<?php

namespace Draw\Bundle\SonataExtraBundle\ActionableAdmin\EventListener;

use Draw\Bundle\SonataExtraBundle\ActionableAdmin\Event\PostExecutionEvent;
use Draw\Bundle\SonataExtraBundle\ActionableAdmin\ObjectActionExecutioner;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PostExecutionResponseListener
{
    public const RESPONSE_ADMIN_ROUTE_PRIORITY = 'response.adminRoutePriorities';
    public const RESPONSE_FALLBACK_ROUTE = 'response.fallbackRoute';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private array $adminRoutePriorities = ['show', 'edit', 'list'],
        private string $fallbackRoute = 'sonata_admin_dashboard',
    ) {
    }

    #[AsEventListener]
    public function onPostExecutionEvent(PostExecutionEvent $event): void
    {
        if ($event->getResponse()) {
            return;
        }

        $event->setResponse(
            $this->createResponse(
                $event->getObjectActionExecutioner()
            )
        );
    }

    private function createResponse(ObjectActionExecutioner $objectActionExecutioner): ?Response
    {
        $admin = $objectActionExecutioner->getAdmin();

        $subject = $objectActionExecutioner->getSubject();

        // This is when object got deleted
        if ($subject && null === $admin->id($subject)) {
            $subject = null;
        }

        $routes = $admin->getRoutes();

        $objectIdParameter = $admin->getIdParameter();

        $adminRoutePriorities = $objectActionExecutioner->options[self::RESPONSE_ADMIN_ROUTE_PRIORITY] ?? $this->adminRoutePriorities;
        $fallbackRoute = $objectActionExecutioner->options[self::RESPONSE_FALLBACK_ROUTE] ?? $this->fallbackRoute;

        foreach ($adminRoutePriorities as $route) {
            if (!$routes->has($route)) {
                continue;
            }

            $forObject = str_contains($routes->get($route)->getPath(), $objectIdParameter);

            if ($forObject && $subject) {
                if (!$admin->hasAccess($route, $subject)) {
                    continue;
                }

                return new RedirectResponse($admin->generateObjectUrl($route, $subject));
            }

            if ($forObject) {
                continue;
            }

            if (!$admin->hasAccess($route)) {
                continue;
            }

            return new RedirectResponse(
                $admin->generateUrl(
                    $route,
                    'list' === $route ? $admin->getFilterParameters() : []
                )
            );
        }

        return $fallbackRoute
            ? new RedirectResponse($this->urlGenerator->generate($fallbackRoute))
            : null;
    }
}
