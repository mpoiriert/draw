<?php namespace Draw\Bundle\DashboardBundle\Annotations;

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
     * @var boolean
     *
     * @Serializer\Exclude()
     */
    private $dialog = false;

    public function __construct(array $values = [])
    {
        if(!array_key_exists('isInstanceTarget', $values)) {
            $values['isInstanceTarget'] = false;
        }

        if (!array_key_exists('button', $values)) {
            $values['button'] = $button = new Button(['label' => 'create']);
        }

        if (!array_key_exists('flow', $values)) {
            $values['flow'] = $flow = new FormFlow(
                [
                    'buttons' => [
                        new Button(
                            [
                                'id' => 'cancel',
                                'label' => 'cancel',
                                'style' => 'stroked-button',
                                'behaviours' => ['cancel']
                            ]
                        ),
                        new Button(
                            [
                                'id' => 'save',
                                'label' => 'save',
                                'style' => 'flat-button',
                                'color' => 'primary',
                                'behaviours' => ['submit', 'save', 'then-edit']
                            ]
                        ),
                        new Button(
                            [
                                'id' => 'save-then-create',
                                'label' => 'saveThenCreate',
                                'style' => 'flat-button',
                                'color' => 'primary',
                                'behaviours' => ['submit', 'save', 'then-create']
                            ]
                        ),
                        new Button(
                            [
                                'id' => 'save-then-list',
                                'label' => 'saveThenList',
                                'style' => 'flat-button',
                                'color' => 'primary',
                                'behaviours' => ['submit', 'save', 'then-list']
                            ]
                        )
                    ],
                    'dialog' => $values['dialog'] ?? $this->dialog
                ]
            );
        }

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