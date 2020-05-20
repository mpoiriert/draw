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
    private $columns = [];

    /**
     * @var array
     */
    private $filters = [];

    public function __construct(array $values = [])
    {
        $values = array_merge(
            ['isInstanceTarget' => false],
            $values
        );

        parent::__construct($values);
    }

    public function getPaginated(): bool
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

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function setFilters(array $filters): void
    {
        $this->filters = $filters;
    }
}