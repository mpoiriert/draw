<?php

namespace Draw\Component\Log\Symfony\EventListener;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class SlowRequestLoggerListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            TerminateEvent::class => ['onKernelTerminate', 2048],
        ];
    }

    public function __construct(private LoggerInterface $logger, private array $requestMatchers)
    {
    }

    public function onKernelTerminate(TerminateEvent $event): void
    {
        $request = $event->getRequest();

        if (!$startTime = $request->server->get('REQUEST_TIME_FLOAT')) {
            return;
        }

        $request = $event->getRequest();

        $durations = [];
        foreach ($this->requestMatchers as $duration => $requestMatchers) {
            /** @var RequestMatcherInterface $requestMatcher */
            foreach ($requestMatchers as $requestMatcher) {
                if ($requestMatcher->matches($request)) {
                    $durations[] = $duration;
                    break;
                }
            }
        }

        if (!$durations) {
            return;
        }

        $durationThreshold = min($durations);

        $duration = (microtime(true) - (float) $startTime) * 1000;

        if ($duration < $durationThreshold) {
            return;
        }

        $this->logger->log(
            LogLevel::WARNING,
            'Response time too slow ({duration} milliseconds) for {url}',
            [
                'url' => $event->getRequest()->getRequestUri(),
                'duration' => $duration,
                'durationThreshold' => $durationThreshold,
            ]
        );
    }
}
