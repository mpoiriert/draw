<?php

namespace Draw\Bundle\DashboardBundle\Feedback;

use Draw\Bundle\DashboardBundle\Client\FeedbackInterface;
use JsonSerializable;

class Notification implements FeedbackInterface, JsonSerializable
{
    public const TYPE_SUCCESS = 'success';

    private $message;
    private $type;

    public function __construct(string $type, string $message)
    {
        $this->message = $message;
        $this->type = $type;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getFeedbackType(): string
    {
        return 'notification';
    }

    public function jsonSerialize()
    {
        return [
            'message' => $this->message,
            'type' => $this->type,
        ];
    }
}
