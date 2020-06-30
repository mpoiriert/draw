<?php namespace Draw\Bundle\DashboardBundle\Event;

use Doctrine\ORM\QueryBuilder;
use Draw\Bundle\DashboardBundle\Doctrine\PaginatorBuilder;
use Symfony\Contracts\EventDispatcher\Event;

class PaginatorBuilderBuildEvent extends Event
{
    private $paginatorBuilder;

    private $queryBuilder;

    public function __construct(PaginatorBuilder $paginatorBuilder, QueryBuilder $queryBuilder)
    {
        $this->paginatorBuilder = $paginatorBuilder;
        $this->queryBuilder = $queryBuilder;
    }

    public function getPaginatorBuilder(): PaginatorBuilder
    {
        return $this->paginatorBuilder;
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }
}