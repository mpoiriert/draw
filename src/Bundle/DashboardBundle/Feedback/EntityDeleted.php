<?php

namespace Draw\Bundle\DashboardBundle\Feedback;

use Draw\Bundle\DashboardBundle\Client\FeedbackInterface;
use JsonSerializable;

/**
 * @todo We should have a way to identify the entity that has been deleted
 */
class EntityDeleted implements FeedbackInterface, JsonSerializable
{
    public function getFeedbackType(): string
    {
        return 'entity-deleted';
    }

    public function jsonSerialize()
    {
        return [
        ];
    }
}
