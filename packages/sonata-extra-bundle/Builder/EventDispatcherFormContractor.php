<?php

namespace Draw\Bundle\SonataExtraBundle\Builder;

use Draw\Bundle\SonataExtraBundle\Event\FormContractorDefaultOptionsEvent;
use Sonata\AdminBundle\Builder\FormContractorInterface;
use Sonata\AdminBundle\FieldDescription\FieldDescriptionInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class EventDispatcherFormContractor implements FormContractorInterface
{
    private FormContractorInterface $decoratedFormContractor;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        FormContractorInterface $decoratedFormContractor,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->decoratedFormContractor = $decoratedFormContractor;

        $this->eventDispatcher = $eventDispatcher;
    }

    public function fixFieldDescription(FieldDescriptionInterface $fieldDescription): void
    {
        $this->decoratedFormContractor->fixFieldDescription($fieldDescription);
    }

    public function getFormBuilder(string $name, array $formOptions = []): FormBuilderInterface
    {
        return $this->decoratedFormContractor->getFormBuilder($name, $formOptions);
    }

    public function getDefaultOptions(
        ?string $type,
        FieldDescriptionInterface $fieldDescription,
        array $formOptions = []
    ): array {
        $defaultOptions = $this->decoratedFormContractor->getDefaultOptions($type, $fieldDescription, $formOptions);

        $this->eventDispatcher->dispatch(
            $event = new FormContractorDefaultOptionsEvent($type, $fieldDescription, $formOptions, $defaultOptions)
        );

        return $event->getDefaultOptions();
    }
}
