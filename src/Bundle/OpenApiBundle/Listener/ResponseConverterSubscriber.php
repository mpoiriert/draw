<?php namespace Draw\Bundle\OpenApiBundle\Listener;

use Draw\Component\OpenApi\Event\PreSerializerResponseEvent;
use Draw\Bundle\OpenApiBundle\View\View;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ResponseConverterSubscriber implements EventSubscriberInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var SerializationContextFactoryInterface
     */
    private $serializationContextFactory;

    /**
     * If we must serialize null
     *
     * @var boolean
     */
    private $serializeNull;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public static function getSubscribedEvents()
    {
        // Must be executed before SensioFrameworkExtraBundle's listener
        return [
            KernelEvents::VIEW => ['onKernelView', 30],
            KernelEvents::RESPONSE => ['onKernelResponse', 30]
        ];
    }

    public function __construct(
        SerializerInterface $serializer,
        SerializationContextFactoryInterface $serializationContextFactory,
        EventDispatcherInterface $eventDispatcher,
        $serializeNull
    ) {
        $this->serializationContextFactory = $serializationContextFactory;
        $this->serializer = $serializer;
        $this->serializeNull = $serializeNull;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function onKernelView(ViewEvent $event)
    {
        $request = $event->getRequest();
        $result = $event->getControllerResult();

        if ($result instanceof Response) {
            return;
        }

        switch ($requestFormat = $request->getRequestFormat()) {
            case 'json':
            case 'xml':
                break;
            default:
                return;
        }

        if (is_null($result)) {
            $event->setResponse(new Response('', 204));
            return;
        }

        $context = $this->serializationContextFactory->createSerializationContext();
        $context->setSerializeNull($this->serializeNull);

        $view = $request->attributes->get('_draw_open_api');

        if ($view instanceof View) {
            if ($version = $view->getSerializerVersion()) {
                $context->setVersion($version);
            }

            if ($groups = $view->getSerializerGroups()) {
                $context->setGroups($groups);
            }
        }

        $this->eventDispatcher->dispatch(new PreSerializerResponseEvent($result, $view, $context));

        $data = $this->serializer->serialize($result, $requestFormat, $context);
        $response = new JsonResponse($data, 200, ['Content-Type' => 'application/' . $requestFormat], true);

        if ($view instanceof View && $view->getStatusCode()) {
            $response->setStatusCode($view->getStatusCode());
        }

        $event->setResponse($response);
    }

    public function onKernelResponse(ResponseEvent $responseEvent)
    {
        if($responseHeaderBag = $responseEvent->getRequest()->attributes->get('_responseHeaderBag', [])) {
            if($responseHeaderBag instanceof ResponseHeaderBag) {
                $responseEvent->getResponse()->headers->add($responseHeaderBag->allPreserveCase());
            }
        }
    }
}