<?php namespace Draw\Bundle\DashboardBundle\Controller;

use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\DashboardBundle\Event\OptionBuilderEvent;
use Draw\Bundle\OpenApiBundle\Controller\OpenApiController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class OptionsController
{
    private $openApiController;

    private $router;

    private $kernel;

    private $eventDispatcher;

    private $requestStack;

    private $basePath;

    private $urlGenerator;

    public function __construct(
        OpenApiController $openApiController,
        RouterInterface $router,
        HttpKernelInterface $kernel,
        EventDispatcherInterface $eventDispatcher,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->openApiController = $openApiController;
        $this->router = $router;
        $this->kernel = $kernel;
        $this->eventDispatcher = $eventDispatcher;
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    /**
     * @Route(name="draw_dashboard_options", methods={"OPTIONS"}, path="/{req}", requirements={"req":".+"})
     */
    public function __invoke(Request $request)
    {
        if ($methods = $request->headers->get('X-Draw-Dashboard-Methods', null)) {
            $methods = explode(',', $methods);
        }

        $originalContext = $this->router->getContext();
        try {
            return $this->loadOption($request->getPathInfo(), $request, $methods);
        } finally {
            $this->router->setContext($originalContext);
        }
    }

    public function dummyHandling($method, $path, Request $fromRequest = null)
    {
        if (is_null($fromRequest)) {
            $fromRequest = $this->requestStack->getMasterRequest();
        }

        $listener = function (ControllerEvent $controllerEvent) {
            $controllerEvent->setController(function () {
                return new Response('', 204);
            });
            $controllerEvent->stopPropagation();
        };
        $this->eventDispatcher->addListener(
            KernelEvents::CONTROLLER,
            $listener,
            -50
        );

        try {
            $subRequest = $this->createSubRequest($fromRequest, $path, $method);
            $subRequest->attributes->set('_draw_dummy_execution', true);
            $response = $this->kernel->handle(
                $subRequest,
                KernelInterface::SUB_REQUEST
            );
        } finally {
            $this->eventDispatcher->removeListener(
                KernelEvents::CONTROLLER,
                $listener
            );
        }

        return [$subRequest, $response];
    }

    public function loadOption($pathInfo, Request $request, array $methods = null)
    {
        $openApiSchema = $this->openApiController->loadOpenApiSchema();

        $collection = $this->router->getRouteCollection();

        /** @var \Symfony\Component\Routing\Route[] $validRoutes */
        $validRoutes = [];

        $methods = $methods ?: [
            Request::METHOD_HEAD,
            Request::METHOD_GET,
            Request::METHOD_POST,
            Request::METHOD_PUT,
            Request::METHOD_PATCH,
            Request::METHOD_DELETE,
            Request::METHOD_PURGE,
            Request::METHOD_TRACE,
            Request::METHOD_CONNECT,
            Request::METHOD_OPTIONS
        ];

        $context = clone $this->router->getContext();
        foreach ($methods as $method) {
            $context->setMethod($method);
            $this->router->setContext($context);
            try {
                $match = $this->router->match($pathInfo);
                $validRoutes[$method] = $collection->get($match['_route']);
            } catch (MethodNotAllowedException $exception) {
                continue;
            }
        }

        $body = [];
        foreach ($validRoutes as $method => $route) {
            $context->setMethod($method);

            if (is_null($pathItem = $openApiSchema->paths[$route->getPath()] ?? null)) {
                continue;
            }

            if (is_null($operation = $pathItem->getOperations()[strtolower($method)] ?? null)) {
                continue;
            }

            $action = $operation->vendor['x-draw-dashboard-action'] ?? null;
            if (!$action instanceof Action) {
                continue;
            }

            list($subRequest, $response) = $this->dummyHandling($method, $pathInfo, $request);

            if ($response->getStatusCode() === 403) {
                $action->setAccessDenied(true);
                continue;
            }

            $body[$method] = ['x-draw-dashboard-action' => $action];

            $this->eventDispatcher->dispatch(
                new OptionBuilderEvent(
                    $action,
                    $operation,
                    $openApiSchema,
                    $subRequest,
                    $response
                )
            );

            $action->setHref($this->getBasePath() . $pathInfo);
            $action->setMethod($method);
        }

        return $body;
    }

    private function getBasePath(): string
    {
        if (is_null($this->basePath)) {
            $this->basePath = str_replace(
                $this->urlGenerator->generate(
                    'draw_dashboard_options',
                    ['req' => 'dummy']
                ),
                '',
                $this->urlGenerator->generate(
                    'draw_dashboard_options',
                    ['req' => 'dummy'],
                    UrlGeneratorInterface::ABSOLUTE_URL
                )
            );
        }

        return $this->basePath;
    }

    private function createSubRequest(Request $request, $uri, $method): Request
    {
        $subRequest = Request::create($uri, $method, array(), $request->cookies->all(), array(),
            $request->server->all());
        if ($request->hasSession()) {
            $subRequest->setSession($request->getSession());
        }
        return $subRequest;
    }
}