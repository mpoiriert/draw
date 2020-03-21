<?php namespace Draw\Bundle\DashboardBundle\Doctrine;

use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;

class PaginatorBuilder
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function fromRequest($class, Request $request, Query $query = null): Paginator
    {
        $paginator = new Paginator(
            $query ?:  $this->buildQuery($class, $request->query->get('orderBy', [])),
            $request->query->getInt('pageSize')
        );

        $paginator->goToPage($request->query->getInt('pageIndex'));

        return $paginator;
    }

    private function buildQuery($class, array $orderBy): Query
    {
        $query = 'SELECT o FROM ' . $class . ' o';

        if ($orderBy) {
            $query .= ' ORDER BY';
        }

        $orderByQuery = '';
        foreach ($orderBy as $key => $direction) {
            $orderByQuery .= sprintf(
                ' o.%s %s, ',
                $key,
                $direction
            );
        }

        $query .= rtrim($orderByQuery, ', ');

        return $this->managerRegistry->getManagerForClass($class)->createQuery($query);
    }
}