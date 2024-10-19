<?php

namespace Draw\DoctrineExtra\ORM\GraphSchema\Event;

use Draw\DoctrineExtra\ORM\GraphSchema\Context;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[Exclude]
class PrepareContextEvent
{
    public function __construct(
        private Context $context,
    ) {
    }

    public function getContext(): Context
    {
        return $this->context;
    }
}
