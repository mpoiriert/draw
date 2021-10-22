<?php

namespace Draw\Bundle\DashboardBundle\Listener;

use Draw\Bundle\DashboardBundle\Client\FeedbackNotifier;
use Draw\Bundle\DashboardBundle\Feedback\DefaultHeader;
use Draw\Bundle\DashboardBundle\Feedback\SignedOut;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class SecurityExceptionListener implements EventSubscriberInterface
{
    /**
     * @var FeedbackNotifier
     */
    private $feedbackNotifier;

    public static function getSubscribedEvents()
    {
        return [
            AuthenticationFailureEvent::class => ['handleAuthenticationFailure'],
        ];
    }

    public function __construct(FeedbackNotifier $feedbackNotifier)
    {
        $this->feedbackNotifier = $feedbackNotifier;
    }

    public function handleAuthenticationFailure(AuthenticationFailureEvent $event)
    {
        $this->feedbackNotifier->sendFeedback(new SignedOut());
        $this->feedbackNotifier->sendFeedback(new DefaultHeader('Authorization', '', true));
    }
}
