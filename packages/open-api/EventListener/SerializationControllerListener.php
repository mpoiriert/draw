<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draw\Component\OpenApi\EventListener;

use Doctrine\Persistence\Proxy;
use Draw\Component\OpenApi\Serializer\Serialization;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SerializationControllerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    /**
     * Modifies the Request object to apply configuration information found in
     * controllers annotations like the template to render or HTTP caching
     * configuration.
     */
    public function onKernelController(ControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!\is_array($controller) && method_exists($controller, '__invoke')) {
            $controller = [$controller, '__invoke'];
        }

        if (!\is_array($controller)) {
            return;
        }

        $className = $this->getRealClass(\get_class($controller[0]));
        $object = new \ReflectionClass($className);
        $method = $object->getMethod($controller[1]);

        $attribute = $method->getAttributes(Serialization::class, \ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;

        $event->getRequest()->attributes->set('_draw_open_api_serialization', $attribute?->newInstance() ?? new Serialization());
    }

    private static function getRealClass(string $class): string
    {
        if (class_exists(Proxy::class)) {
            if (false === $pos = strrpos($class, '\\'.Proxy::MARKER.'\\')) {
                return $class;
            }

            return substr($class, $pos + Proxy::MARKER_LENGTH + 2);
        }

        return $class;
    }
}
