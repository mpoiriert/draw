<?php namespace Draw\Bundle\DashboardBundle\Annotations\FormLayout;

/**
 * @Annotation
 */
class GridList extends BaseLayout
{
    const TYPE = 'grid-list';

    /**
     * @var int
     */
    private $cols = 1;

    /**
     * @var array<\Draw\Bundle\DashboardBundle\Annotations\FormLayout\GridListTile>
     */
    private $tiles = [];

    public function getCols(): int
    {
        return $this->cols;
    }

    public function setCols(int $cols): void
    {
        $this->cols = $cols;
    }

    public function getTiles(): array
    {
        return $this->tiles;
    }

    public function setTiles(array $tiles): void
    {
        $this->tiles = $tiles;
    }
}