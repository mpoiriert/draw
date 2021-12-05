<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Draw\Bundle\MessengerBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;

class StopWorkerOnSigintSignalListener implements EventSubscriberInterface
{
    public function onWorkerStarted(WorkerStartedEvent $event): void
    {
        pcntl_signal(SIGINT, static function () use ($event) {
            $event->getWorker()->stop();
        });
    }

    public static function getSubscribedEvents()
    {
        if (!\function_exists('pcntl_signal')) {
            return [];
        }

        return [
            WorkerStartedEvent::class => ['onWorkerStarted', 100],
        ];
    }
}
