<?php namespace Draw\Bundle\DashboardBundle\Client;

use Draw\Bundle\OpenApiBundle\Listener\ResponseConverterSubscriber;
use Symfony\Component\HttpFoundation\RequestStack;

class FeedbackNotifier
{
    const HEADER_NAME = 'X-Draw-Feedback';

    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function sendFeedback(FeedbackInterface $feedback)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return;
        }

        ResponseConverterSubscriber::setResponseHeader(
            $request,
            self::HEADER_NAME,
            [json_encode(['type' => $feedback->getFeedbackType(), 'metadata' => $feedback])],
            false
        );
    }
}