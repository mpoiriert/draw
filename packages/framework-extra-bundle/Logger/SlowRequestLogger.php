<?php

namespace Draw\Bundle\FrameworkExtraBundle\Logger;

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class SlowRequestLogger implements EventSubscriberInterface
{
    private LoggerInterface $logger;

    private array $requestMatchers;

    public static function getSubscribedEvents(): array
    {
        return [
            TerminateEvent::class => ['onKernelTerminate', 2048],
        ];
    }

    public function __construct(LoggerInterface $logger, array $requestMatchers)
    {
        $this->logger = $logger;
        $this->requestMatchers = $requestMatchers;
    }

    public function onKernelTerminate(TerminateEvent $event)
    {
        if (!isset($_SERVER['REQUEST_TIME_FLOAT'])) {
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

        $duration = (microtime(true) - (float) $_SERVER['REQUEST_TIME_FLOAT']) * 1000;

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
