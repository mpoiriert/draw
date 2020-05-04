<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class FormFlow extends Flow implements FlowWithButtonsInterface
{
    const TYPE = 'form';

    /**
     * @var array<\Draw\Bundle\DashboardBundle\Annotations\Button>
     */
    private $buttons;

    /**
     * @var boolean
     */
    private $dialog = false;

    public function getButtons(): array
    {
        return $this->buttons;
    }

    public function setButtons(array $buttons): void
    {
        $this->buttons = $buttons;
    }

    public function isDialog(): bool
    {
        return $this->dialog;
    }

    public function setDialog(bool $dialog): void
    {
        $this->dialog = $dialog;
    }
}