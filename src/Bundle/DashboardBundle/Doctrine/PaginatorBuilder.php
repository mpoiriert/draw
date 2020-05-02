<?php namespace Draw\Bundle\DashboardBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
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
        if (is_null($query)) {
            $query = $this->buildQuery(
                $class,
                $request->query->get('orderBy', []),
                $request->query->get('filters', [])
            );
        }
        $paginator = new Paginator(
            $query,
            $request->query->getInt('pageSize')
        );

        $paginator->goToPage($request->query->getInt('pageIndex'));

        return $paginator;
    }

    private function buildQuery($class, array $orderBy, array $filters): Query
    {
        /** @var EntityManagerInterface $manager */
        $manager = $this->managerRegistry->getManagerForClass($class);

        $queryBuilder = $manager->createQueryBuilder()
            ->from($class, 'o')
            ->select('o');

        foreach ($orderBy as $key => $direction) {
            $queryBuilder->addOrderBy('o.' . $key, $direction);
        }

        foreach ($filters as $filter) {
            $value = $filter['value'];
            if ($value === '') {
                continue;
            }

            $key = $filter['id'];
            $comparison = $filter['comparison'];

            switch (true) {
                case is_array($value):
                    $whereString =  'o.%s %s (:%s)';
                    break;
                default:
                    $whereString =  'o.%s %s :%s';
                    break;
            }

            $queryBuilder
                ->andWhere(sprintf(
                    $whereString,
                    $key,
                    $comparison,
                    $key))
                ->setParameter($key, $value);
        }

        return $queryBuilder->getQuery();
    }
}