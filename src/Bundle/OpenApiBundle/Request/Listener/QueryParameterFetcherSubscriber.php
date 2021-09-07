<?php

namespace Draw\Bundle\OpenApiBundle\Request\Listener;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Schema\QueryParameter;
use InvalidArgumentException;
use ReflectionMethod;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class QueryParameterFetcherSubscriber implements EventSubscriberInterface
{
    /**
     * @var Reader
     */
    private $reader;

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => ['onKernelController', 5],
        ];
    }

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Core controller handler.
     *
     * @throws InvalidArgumentException
     */
    public function onKernelController(ControllerEvent $event)
    {
        $request = $event->getRequest();
        $controller = $event->getController();

        if (!is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        if (!is_array($controller)) {
            return;
        }

        $annotations = $this->reader->getMethodAnnotations(
            new ReflectionMethod(get_class($controller[0]), $controller[1])
        );

        $parameters = [];
        foreach ($annotations as $annotation) {
            if (!$annotation instanceof QueryParameter) {
                continue;
            }
            $parameters[] = $annotation;

            $name = $annotation->name;

            if ($request->attributes->has($name) && null !== $request->attributes->get($name)) {
                throw new InvalidArgumentException(
                    sprintf(
                        "QueryParameterFetcherSubscriber parameter conflicts with a path parameter '%s' for route '%s'",
                        $name,
                        $request->attributes->get('_route')
                    )
                );
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
                        $value = $request->query->getDigits($name);
                        break;
                    case 'array':
                        switch ($annotation->collectionFormat) {
                            case 'csv':
                                $separator = ',';
                                break;
                            case 'ssv':
                                $separator = ' ';
                                break;
                            case 'tsv':
                                $separator = "\n";
                                break;
                            case 'pipes':
                                $separator = '|';
                                break;
                            case 'multi':
                            default:
                                throw new RuntimeException(sprintf(
                                    'Unsupported collection format [%s]',
                                    $annotation->collectionFormat
                                ));

                        }
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
