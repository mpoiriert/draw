<?php

namespace Draw\Bundle\DashboardBundle\Feedback;

use Draw\Bundle\DashboardBundle\Client\FeedbackInterface;

class SignedOut implements FeedbackInterface
{
    public const FEEDBACK_TYPE = 'signed-out';

    public function getFeedbackType(): string
    {
        return static::FEEDBACK_TYPE;
    }
}
