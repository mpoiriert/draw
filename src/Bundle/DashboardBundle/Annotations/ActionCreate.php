<?php namespace Draw\Bundle\DashboardBundle\Annotations;

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

    public function __construct(array $values = [])
    {
        if (!array_key_exists('button', $values)) {
            $values['button'] = $button = new Button(['label' => 'create']);
        }

        if (!array_key_exists('flow', $values)) {
            $values['flow'] = $button = new FormFlow(
                [
                    'buttons' => [
                        new Button(
                            [
                                'label' => 'cancel',
                                'style' => 'stroked-button',
                                'behaviours' => ['cancel']
                            ]
                        ),
                        new Button(
                            [
                                'label' => 'save',
                                'style' => 'flat-button',
                                'color' => 'primary',
                                'behaviours' => ['submit']
                            ]
                        )
                    ]
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
}