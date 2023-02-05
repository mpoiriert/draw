<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Event\PreSerializerResponseEvent;
use Draw\Component\OpenApi\Serializer\Serialization;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ResponseSerializerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        // Must be executed before SensioFrameworkExtraBundle's listener
        return [
            KernelEvents::VIEW => ['onKernelView', 30],
            KernelEvents::RESPONSE => ['onKernelResponse', 30],
        ];
    }

    public function __construct(
        private SerializerInterface $serializer,
        private SerializationContextFactoryInterface $serializationContextFactory,
        private EventDispatcherInterface $eventDispatcher,
        private bool $serializeNull
    ) {
    }

    public function onKernelView(ViewEvent $event): void
    {
        $request = $event->getRequest();
        $result = $event->getControllerResult();

        if ($result instanceof Response) {
            return;
        }

        switch ($requestFormat = $request->getRequestFormat()) {
            case 'json':
                break;
            default:
                return;
        }

        if (null === $result) {
            $event->setResponse(new Response('', Response::HTTP_NO_CONTENT));

            return;
        }

        $context = $this->serializationContextFactory->createSerializationContext();
        $context->setSerializeNull($this->serializeNull);

        $serialization = $request->attributes->get('_draw_open_api_serialization', new Serialization());

        \assert($serialization instanceof Serialization);

        if ($version = $serialization->serializerVersion) {
            $context->setVersion($version);
        }

        if ($groups = $serialization->serializerGroups) {
            $context->setGroups($groups);
        }

        foreach ($serialization->contextAttributes as $key => $value) {
            $context->setAttribute($key, $value);
        }

        $this->eventDispatcher->dispatch(new PreSerializerResponseEvent($result, $serialization, $context));

        $data = $this->serializer->serialize($result, $requestFormat, $context);
        $response = new JsonResponse($data, 200, ['Content-Type' => 'application/'.$requestFormat], true);

        if ($serialization->statusCode) {
            $response->setStatusCode($serialization->statusCode);
        }

        $event->setResponse($response);
    }

    public function onKernelResponse(ResponseEvent $responseEvent): void
    {
        if ($responseHeaderBag = $responseEvent->getRequest()->attributes->get('_responseHeaderBag')) {
            if ($responseHeaderBag instanceof ResponseHeaderBag) {
                $responseEvent->getResponse()->headers->add($responseHeaderBag->allPreserveCase());
            }
        }
    }

    /**
     * @see ResponseHeaderBag::set
     */
    public static function setResponseHeader(Request $request, string $key, mixed $values, bool $replace = true): void
    {
        $responseHeaderBag = $request->attributes->get('_responseHeaderBag', new ResponseHeaderBag());
        if (!$responseHeaderBag instanceof ResponseHeaderBag) {
            throw new \RuntimeException('The current attribute value of [_responseHeaderBag] is invalid');
        }

        $responseHeaderBag->set($key, $values, $replace);
        $request->attributes->set('_responseHeaderBag', $responseHeaderBag);
    }
}
