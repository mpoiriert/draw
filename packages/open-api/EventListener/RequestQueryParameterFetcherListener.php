<?php

namespace Draw\Component\OpenApi\EventListener;

use Draw\Component\OpenApi\Schema\QueryParameter;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class RequestQueryParameterFetcherListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 5],
        ];
    }

    public function onKernelController(ControllerEvent $event): void
    {
        $request = $event->getRequest();
        $controller = $event->getController();

        if (!\is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        if (!\is_array($controller)) {
            return;
        }

        $controllerObject = $controller[0];

        if (!\is_object($controllerObject)) {
            return;
        }

        $queryParameters = QueryParameter::fromReflectionMethod(
            new \ReflectionMethod($controllerObject::class, $controller[1])
        );

        foreach ($queryParameters as $queryParameter) {
            $name = $queryParameter->name;

            if ($request->attributes->has($name) && null !== $request->attributes->get($name)) {
                throw new \InvalidArgumentException(\sprintf(
                    'QueryParameterFetcherSubscriber parameter conflicts with a path parameter [%s] for route [%s]',
                    $name,
                    $request->attributes->get('_route')
                ));
            }

            if ($request->query->has($name)) {
                switch ($queryParameter->type) {
                    case 'string':
                        $value = (string) $request->query->get($name);
                        break;
                    case 'integer':
                        $value = $request->query->getInt($name);
                        break;
                    case 'boolean':
                        $value = $request->query->getBoolean($name);
                        break;
                    case 'number':
                        /** @var @phpstan-ignore-next-line $value */
                        $value = $request->query->get($name) + 0;
                        break;
                    case 'array':
                        $separator = match ($queryParameter->collectionFormat) {
                            'csv' => ',',
                            'ssv' => ' ',
                            'tsv' => "\t",
                            'pipes' => '|',
                            // no break
                            default => throw new \RuntimeException(\sprintf(
                                'Unsupported collection format [%s]',
                                $queryParameter->collectionFormat
                            )),
                        };
                        $value = explode($separator, (string) $request->query->get($name));
                        break;
                    default:
                        $value = $request->query->get($name);
                        break;
                }

                $request->attributes->set($name, $value);
            }
        }

        if ($queryParameters) {
            $request->attributes->set('_draw_query_parameters_validation', $queryParameters);
        }
    }
}
