<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class FormFlow extends Flow implements FlowWithButtonsInterface
{
    const TYPE = 'form';

    /**
     * @Serializer\Accessor(getter="getId")
     *
     * @internal
     */
    private $id;


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

    public function getId()
    {
        if(is_null($this->id)) {
            $this->id = uniqid();
        }

        return $this->id;
    }
}