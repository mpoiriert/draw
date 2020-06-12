<?php

namespace Draw\Bundle\DashboardBundle\Feedback;

use Draw\Bundle\DashboardBundle\Client\FeedbackInterface;

class SignedIn implements FeedbackInterface
{
    const FEEDBACK_TYPE = 'signed-in';

    public function getFeedbackType(): string
    {
        return static::FEEDBACK_TYPE;
    }
}
