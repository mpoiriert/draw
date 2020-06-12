<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Bundle\DashboardBundle\Annotations\Button as Button;
use JMS\Serializer\Annotation as Serializer;

/**
 * @Annotation
 */
class ActionCreate extends Action
{
    const TYPE = 'create';

    /**
     * @var array
     */
    private $inputs;

    private $default;

    /**
     * @var bool
     *
     * @Serializer\Exclude()
     */
    private $dialog = false;

    public function __construct(array $values = [])
    {
        $dialog = $values['dialog'] ?? $this->dialog;
        $values = array_merge(
            [
                'isInstanceTarget' => false,
                'button' => new Button\ButtonCreate(),
                'flow' => new FormFlow(
                    [
                        'buttons' => !$dialog ? [
                                new Button\ButtonCancel(),
                                new Button\ButtonSave(),
                                new Button\ButtonSaveThenCreate(),
                                new Button\ButtonSaveThenList(),
                            ] : [
                                new Button\ButtonCancel(),
                                new Button\ButtonSave(['thenList' => []]),
                            ],
                        'dialog' => $dialog,
                    ]
                ),
            ],
            $values
        );

        parent::__construct($values);
    }

    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function setInputs(array $inputs): void
    {
        $this->inputs = $inputs;
    }

    public function getDefault()
    {
        return $this->default;
    }

    public function setDefault($default): void
    {
        $this->default = $default;
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
