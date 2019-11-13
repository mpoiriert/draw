<?php namespace Draw\Component\Messenger\Middleware;

use Draw\Component\Messenger\Message\ManuallyTriggeredInterface;
use Draw\Component\Messenger\Stamp\ManualTriggerStamp;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class ManualTriggerStamperMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        if(($message = $envelope->getMessage()) instanceof ManuallyTriggeredInterface) {
            if(!$envelope->last(ManualTriggerStamp::class)) {
                $envelope = $envelope->with(new ManualTriggerStamp());
            }
        }

        return $stack->next()->handle($envelope, $stack);
    }
}