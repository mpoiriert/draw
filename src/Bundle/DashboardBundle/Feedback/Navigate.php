<?php

namespace Draw\Bundle\DashboardBundle\Feedback;

use Draw\Bundle\DashboardBundle\Annotations\Action;
use Draw\Bundle\DashboardBundle\Client\FeedbackInterface;
use JsonSerializable;

class Navigate implements FeedbackInterface, JsonSerializable
{
    private $action;

    public function __construct(Action $action)
    {
        $this->action = $action;
    }

    public function getFeedbackType(): string
    {
        return 'navigate';
    }

    public function jsonSerialize()
    {
        return [
            'href' => $this->action->getHref(),
            'name' => $this->action->getName(),
            'method' => $this->action->getMethod(),
            'type' => $this->action->getType(),
        ];
    }
}
