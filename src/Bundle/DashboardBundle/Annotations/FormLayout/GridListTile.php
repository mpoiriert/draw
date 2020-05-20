<?php namespace Draw\Bundle\DashboardBundle\Annotations\FormLayout;

use function Draw\Bundle\DashboardBundle\construct;

/**
 * @Annotation
 */
class GridListTile
{
    /**
     * @var int
     */
    private $colspan = 1;

    /**
     * @var int
     */
    private $rowspan = 1;

    /**
     * @var array<string>
     */
    private $inputs;

    public function __construct(array $values = [])
    {
        construct($this, $values);
    }

    public function getColspan(): int
    {
        return $this->colspan;
    }

    public function setColspan(int $colspan): void
    {
        $this->colspan = $colspan;
    }

    public function getRowspan(): int
    {
        return $this->rowspan;
    }

    public function setRowspan(int $rowspan): void
    {
        $this->rowspan = $rowspan;
    }

    public function getInputs(): array
    {
        return $this->inputs;
    }

    public function setInputs(array $inputs): void
    {
        $this->inputs = $inputs;
    }
}