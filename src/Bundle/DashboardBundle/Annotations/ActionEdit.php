<?php namespace Draw\Bundle\DashboardBundle\Annotations;

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
        if(!array_key_exists('isInstanceTarget', $values)) {
            $values['isInstanceTarget'] = true;
        }

        if (!array_key_exists('button', $values)) {
            $values['button'] = $button = new Button(['label' => 'edit', 'icon' => 'edit']);
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