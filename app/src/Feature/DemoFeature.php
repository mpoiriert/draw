<?php

namespace App\Feature;

use Draw\Component\Application\Feature\Attribute\Config;
use Draw\Component\Application\Feature\FeatureInitializer;
use Draw\Component\Application\Feature\SelfInitializeFeatureInterface;

class DemoFeature implements SelfInitializeFeatureInterface
{
    #[Config]
    private bool $enabled = true;

    private ?int $limit = null;

    public function __construct(FeatureInitializer $featureInitializer)
    {
        $featureInitializer->initialize($this);
    }

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
