<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class FormFlow extends Flow
{
    const TYPE = 'form';

    /**
     * @var array<\Draw\Bundle\DashboardBundle\Annotations\Button>
     */
    public $buttons;

    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function setButtons(array $buttons): void
    {
        $this->buttons = $buttons;
    }
}