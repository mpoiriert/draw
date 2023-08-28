<?php

namespace Draw\Bundle\FrameworkExtraBundle\Feature;

use Draw\Component\Application\Feature\FeatureInitializer;
use Draw\Component\Application\Feature\FeatureInterface;

abstract class ServiceFeature implements FeatureInterface
{
    public function __construct(FeatureInitializer $featureInitializer)
    {
        $featureInitializer->initialize($this);
    }
}
