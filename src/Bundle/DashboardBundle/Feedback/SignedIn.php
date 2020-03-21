<?php namespace Draw\Bundle\DashboardBundle\Feedback;

use Draw\Bundle\DashboardBundle\Client\FeedbackInterface;

class SignedIn implements FeedbackInterface
{
    public function getFeedbackType(): string
    {
        return 'signed-in';
    }
}