<?php namespace Draw\Bundle\OpenApiBundle\Cors\Listener;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\RouterInterface;

class CorsResponseSubscriber implements EventSubscriberInterface
{
    private $exposedHeaders;

    private $allowedHeaders;

    private $allowAllHeaders;

    private $allowCredentials;

    private $allowAllOrigins;

    private $router;

    public static function getSubscribedEvents()
    {
        return [
            RequestEvent::class => [
                ['responseForCors', 35]
            ],
            ResponseEvent::class => 'addCorsHeader'
        ];
    }

    private $logger;

    public function __construct(
        LoggerInterface $logger,
        RouterInterface $router,
        array $exposedHeaders = [],
        array $allowedHeaders = [],
        bool $allowAllHeaders = false,
        bool $allowCredentials = false,
        bool $allowAllOrigins = true
    ) {
        $this->router = $router;
        $this->logger = $logger;
        $this->exposedHeaders = $exposedHeaders;
        $this->allowedHeaders = $allowedHeaders;
        $this->allowAllOrigins = $allowAllOrigins;
        $this->allowCredentials = $allowCredentials;
        $this->allowAllHeaders = $allowAllHeaders;
    }

    public function responseForCors(RequestEvent $event)
    {
        $request = $event->getRequest();
        switch (true) {
            case $request->getMethod() !== Request::METHOD_OPTIONS:
            case !$request->headers->has('Origin'):
            case !($method = $request->headers->get('Access-Control-Request-Method')):
                return;
        }

        $allowedMethods = $this->getAllowedMethods($request->getPathInfo());
        $response = new Response(
            '',
            204,
            [
                'Access-Control-Allow-Methods' => implode(' ,', $allowedMethods)
            ]
        );

        $event->setResponse($response);
    }

    private function getAllowedMethods($pathInfo)
    {
        $methods = [
            Request::METHOD_HEAD,
            Request::METHOD_GET,
            Request::METHOD_POST,
            Request::METHOD_PUT,
            Request::METHOD_PATCH,
            Request::METHOD_DELETE,
            Request::METHOD_PURGE,
            Request::METHOD_TRACE,
            Request::METHOD_OPTIONS,
            Request::METHOD_CONNECT,
        ];

        $context = $originalContext = $this->router->getContext();
        $context = clone $context;

        $allowedMethods = [];
        try {
            $this->router->setContext($context);
            foreach ($methods as $method) {
                $context->setMethod($method);
                try {
                    $this->router->match($pathInfo);
                    $allowedMethods[] = $method;
                } catch (ResourceNotFoundException $exception) {
                    continue;
                } catch (MethodNotAllowedException $exception) {
                    continue;
                }
            }
        } finally {
            $this->router->setContext($originalContext);
        }

        return $allowedMethods;
    }

    public function addCorsHeader(ResponseEvent $responseEvent)
    {
        $request = $responseEvent->getRequest();
        $response = $responseEvent->getResponse();

        if ($request->attributes->get('_draw_dummy_execution')) {
            return;
        }

        $allowedHeaders = $this->allowedHeaders;

        if ($this->allowAllHeaders) {
            $requestHeaders = explode(',', $request->headers->get('Access-Control-Request-Headers', ''));
            $allowedHeaders = array_merge($requestHeaders, $allowedHeaders);
        }

        $allowedHeaders = array_unique(
            array_filter(
                array_map(
                    'strtolower',
                    array_map('trim', $allowedHeaders)
                )
            )
        );

        if ($allowedHeaders) {
            $response->headers->set("Access-Control-Allow-Headers", implode(', ', $allowedHeaders));
        }

        if ($this->allowAllOrigins) {
            $response->headers->set('Access-Control-Allow-Origin', $request->headers->get('Origin'));
        }

        if ($this->allowCredentials) {
            $response->headers->set('Access-Control-Allow-Credentials', 'true');
        }

        if ($this->exposedHeaders) {
            $response->headers->set('Access-Control-Expose-Headers', strtolower(implode(', ', $this->exposedHeaders)));
        }
    }
}