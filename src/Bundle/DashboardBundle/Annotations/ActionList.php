<?php namespace Draw\Bundle\DashboardBundle\Annotations;

/**
 * @Annotation
 */
class ActionList extends Action
{
    const TYPE = 'list';

    /**
     * @var bool
     */
    private $paginated = true;

    /**
     * @var array
     */
    private $columns;

    public function isPaginated(): bool
    {
        return $this->paginated;
    }

    public function setPaginated(bool $paginated): void
    {
        $this->paginated = $paginated;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): void
    {
        $this->columns = $columns;
    }
}