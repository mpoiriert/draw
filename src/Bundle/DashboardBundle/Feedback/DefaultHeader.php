<?php

namespace Draw\Bundle\DashboardBundle\Feedback;

use Draw\Bundle\DashboardBundle\Client\FeedbackInterface;

class DefaultHeader implements FeedbackInterface
{
    const FEEDBACK_TYPE = 'default-header';

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $value;

    /**
     * @var bool
     */
    public $clear = false;

    public function __construct(string $name, ?string $value, bool $clear = false)
    {
        $this->name = $name;
        $this->value = $value;
        $this->clear = $clear;
        if ($this->clear) {
            $this->value = null;
        }
    }

    public function getFeedbackType(): string
    {
        return static::FEEDBACK_TYPE;
    }
}
