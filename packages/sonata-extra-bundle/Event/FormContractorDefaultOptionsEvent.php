<?php

namespace Draw\Bundle\SonataExtraBundle\Event;

use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Contracts\EventDispatcher\Event;

class FormContractorDefaultOptionsEvent extends Event
{
    public function __construct(
        private ?string $type,
        private FieldDescriptionInterface $fieldDescription,
        private array $formOptions,
        private array $defaultOptions
    ) {
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function getFieldDescription(): FieldDescriptionInterface
    {
        return $this->fieldDescription;
    }

    public function getFormOptions(): array
    {
        return $this->formOptions;
    }

    public function getDefaultOptions(): array
    {
        return $this->defaultOptions;
    }

    public function setDefaultOptions(array $defaultOptions): void
    {
        $this->defaultOptions = $defaultOptions;
    }
}
