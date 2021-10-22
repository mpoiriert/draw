<?php

namespace Draw\Bundle\DashboardBundle\Feedback;

use Draw\Bundle\DashboardBundle\Client\FeedbackInterface;
use JsonSerializable;

class OpenLink implements FeedbackInterface, JsonSerializable
{
    private $url;

    private $target;

    public function __construct(string $url, string $target = '_blank')
    {
        $this->url = $url;
        $this->target = $target;
    }

    public function getFeedbackType(): string
    {
        return 'open-link';
    }

    public function jsonSerialize()
    {
        return [
            'url' => $this->url,
            'target' => $this->target,
        ];
    }
}
