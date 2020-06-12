<?php

namespace Draw\Bundle\DashboardBundle\Annotations;

use Draw\Bundle\DashboardBundle\Annotations\Button as Button;

/**
 * @Annotation
 */
class ActionEdit extends ActionCreate
{
    const TYPE = 'edit';

    /**
     * @var array
     */
    private $inputs;

    private $default;

    public function __construct(array $values = [])
    {
        $values = array_merge(
            [
                'isInstanceTarget' => true,
                'button' => new Button\ButtonEdit(),
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
}
