<?php namespace Draw\Bundle\DashboardBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
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

        $alias = 'o';
        $queryBuilder = $manager->createQueryBuilder()
            ->from($class, $alias)
            ->select($alias);

        foreach ($orderBy as $key => $direction) {
            $queryBuilder->addOrderBy(sprintf('%s.%s', $alias, $key), $direction);
        }

        $this->addFilters($queryBuilder, $filters, $class, $alias);

        return $queryBuilder->getQuery();
    }

    private function addFilters(QueryBuilder $queryBuilder, array $filters, string $class, string $alias)
    {
        $entityManager = $this->managerRegistry->getManagerForClass($class);
        $classMetadata = $entityManager->getClassMetadata($class);

        foreach ($filters as $filter) {
            $value = $filter['value'];
            if ($value === '') {
                continue;
            }

            $key = $filter['id'];
            $comparison = $filter['comparison'];

            if ($classMetadata->hasAssociation($key)) {
                $this->buildAssociationFilter($queryBuilder, $alias, $classMetadata, $filter);
                continue;
            }

            switch (true) {
                case is_array($value):
                    $whereString = '%s.%s %s (:%s)';
                    break;
                default:
                    $whereString = '%s.%s %s :%s';
                    break;
            }

            switch ($comparison) {
                case 'LIKE':
                case 'NOT LIKE':
                    $value = '%' . $value . '%';
                    break;
            }

            $queryBuilder
                ->andWhere(sprintf(
                    $whereString,
                    $alias,
                    $key,
                    $comparison,
                    $key))
                ->setParameter($key, $value);
        }
    }

    private function buildAssociationFilter(
        QueryBuilder $queryBuilder,
        $alias,
        ClassMetadata $classMetadata,
        array $filter
    ) {
        $value = $filter['value'];
        $key = $filter['id'];
        $comparison = $filter['comparison'];

        $associationClassMetadata = $this->managerRegistry
            ->getManagerForClass($classMetadata->getName())
            ->getClassMetadata($classMetadata->getAssociationTargetClass($key));

        $queryBuilder->join(sprintf('%s.%s', $alias, $key), $key);
        $identifierKey = $associationClassMetadata->getIdentifierFieldNames()[0];
        if (array_key_exists($identifierKey, $value)) {
            $value = $value[$identifierKey];
        } else {
            $value = array_map(
                function ($entry) use ($identifierKey) {
                    return $entry[$identifierKey];
                },
                $value
            );
        }

        $this->addFilters(
            $queryBuilder,
            [
                [
                    'id' => $associationClassMetadata->getIdentifierFieldNames()[0],
                    'value' => $value,
                    'comparison' => $comparison,
                ]
            ],
            $associationClassMetadata->getName(),
            $key
        );
    }
}