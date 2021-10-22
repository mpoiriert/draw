<?php

namespace Draw\Bundle\DashboardBundle\Feedback;

use Draw\Bundle\DashboardBundle\Client\FeedbackInterface;

class SignedIn implements FeedbackInterface
{
    public const FEEDBACK_TYPE = 'signed-in';

    public function getFeedbackType(): string
    {
        return static::FEEDBACK_TYPE;
    }
}
