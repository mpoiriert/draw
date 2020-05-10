<?php namespace Draw\Bundle\DashboardBundle\Feedback;

use Draw\Bundle\DashboardBundle\Client\FeedbackInterface;
use JsonSerializable;

class CloseDialog implements FeedbackInterface, JsonSerializable
{
    private $id;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function getFeedbackType(): string
    {
        return 'close-dialog';
    }

    public function jsonSerialize()
    {
        return [
            'id' => $this->id
        ];
    }
}