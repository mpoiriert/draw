<?php

namespace Draw\Bundle\SonataExtraBundle\Block\Event;

use Sonata\BlockBundle\Block\BlockContextInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FinalizeContextEvent extends Event
{
    public function __construct(private BlockContextInterface $blockContext)
    {
    }

    public function getBlockContext(): BlockContextInterface
    {
        return $this->blockContext;
    }
}
