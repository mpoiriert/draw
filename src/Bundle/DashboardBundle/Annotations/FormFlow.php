<?php namespace Draw\Bundle\DashboardBundle\Annotations;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class FormFlow extends Flow implements FlowWithButtonsInterface
{
    const TYPE = 'form';

    /**
     * The id of flow are use for callback you should not set it manually
     *
     * @var string|null
     */
    private $id;

    /**
     * @var array<\Draw\Bundle\DashboardBundle\Annotations\Button\Button>
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

    public function getId(): ?string
    {
        return $this->id;
    }

    public function setId(?string $id): void
    {
        $this->id = $id;
    }
}