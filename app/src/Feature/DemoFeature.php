<?php

namespace App\Feature;

use Draw\Bundle\FrameworkExtraBundle\Feature\ServiceFeature;
use Draw\Component\Application\Feature\Attribute\Config;
use Draw\Component\Application\Feature\SelfInitializeFeatureInterface;

class DemoFeature extends ServiceFeature implements SelfInitializeFeatureInterface
{
    #[Config]
    private bool $enabled = true;

    private ?int $limit = null;

    public function getName(): string
    {
        return 'acme_demo';
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function initialize(array $configuration): void
    {
        $this->limit = $configuration['limit'] ?? null;
    }
}
