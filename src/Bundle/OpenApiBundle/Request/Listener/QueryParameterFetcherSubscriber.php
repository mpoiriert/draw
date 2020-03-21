<?php namespace Draw\Bundle\OpenApiBundle\Request\Listener;

use Doctrine\Common\Annotations\Reader;
use Draw\Component\OpenApi\Schema\QueryParameter;
use InvalidArgumentException;
use ReflectionMethod;
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
            KernelEvents::CONTROLLER => ['onKernelController', 5]
        ];
    }

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Core controller handler.
     *
     * @param ControllerEvent $event
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

        foreach ($annotations as $annotation) {
            if (!$annotation instanceof QueryParameter) {
                continue;
            }

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
                $request->attributes->set($name, $request->query->get($name));
            }
        }
    }
}