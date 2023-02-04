<?php

namespace Draw\Component\OpenApi\EventListener;

use Doctrine\Common\Annotations\Reader;
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

    public function __construct(private Reader $reader)
    {
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

        $annotations = $this->reader->getMethodAnnotations(
            new \ReflectionMethod($controller[0]::class, $controller[1])
        );

        $parameters = [];
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof QueryParameter) {
                continue;
            }
            $parameters[] = $annotation;

            $name = $annotation->name;

            if ($request->attributes->has($name) && null !== $request->attributes->get($name)) {
                throw new \InvalidArgumentException(sprintf('QueryParameterFetcherSubscriber parameter conflicts with a path parameter [%s] for route [%s]', $name, $request->attributes->get('_route')));
            }

            if ($request->query->has($name)) {
                switch ($annotation->type) {
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
                        $separator = match ($annotation->collectionFormat) {
                            'csv' => ',',
                            'ssv' => ' ',
                            'tsv' => "\t",
                            'pipes' => '|',
                            // no break
                            default => throw new \RuntimeException(sprintf('Unsupported collection format [%s]', $annotation->collectionFormat)),
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

        if ($parameters) {
            $request->attributes->set('_draw_query_parameters_validation', $parameters);
        }
    }
}
