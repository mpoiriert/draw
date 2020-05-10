<?php namespace Draw\Bundle\DashboardBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Mapping\ClassMetadata;
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

    public function fromRequest($class, Request $request, callable $queryBuilderCallback = null): Paginator
    {
        $queryBuilder = $this->buildQueryBuilder(
            $class,
            $request->query->get('orderBy', []),
            $request->query->get('filters', [])
        );

        if($queryBuilderCallback) {
            call_user_func($queryBuilderCallback, $queryBuilder);
        }

        $paginator = new Paginator(
            $queryBuilder->getQuery(),
            $request->query->getInt('pageSize')
        );

        $paginator->goToPage($request->query->getInt('pageIndex'));

        return $paginator;
    }

    private function buildQueryBuilder($class, array $orderBy, array $filters): QueryBuilder
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

        return $queryBuilder;
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

            $whereString = '%s.%s %s :%s';

            $parameterName = $alias . '_' . $key;

            switch (true) {
                case ($comparison === 'BETWEEN'):
                    $value = array_filter($value, function ($value) {
                        return $value !== '';
                    });

                    if (!$value) {
                        return;
                    }

                    if (count($value) === 1) {
                        $this->addFilters(
                            $queryBuilder,
                            [
                                [
                                    'id' => $key,
                                    'comparison' => key($value) === 'from' ? '>=' : '<=',
                                    'value' => current($value)
                                ]
                            ],
                            $class,
                            $alias
                        );
                        return;
                    }

                    $queryBuilder
                        ->andWhere(sprintf(
                            '%s.%s BETWEEN :%sFrom AND :%sTo',
                            $alias,
                            $key,
                            $parameterName,
                            $parameterName
                        ));
                    break;

                /** @noinspection PhpMissingBreakStatementInspection */
                case is_array($value):
                    $whereString = '%s.%s %s (:%s)';
                default:
                    $queryBuilder
                        ->andWhere(sprintf(
                            $whereString,
                            $alias,
                            $key,
                            $comparison,
                            $parameterName
                        ));
                    break;
            }

            switch ($comparison) {
                case 'BETWEEN':
                    $queryBuilder->setParameter($parameterName . 'From', $value['from']);
                    $queryBuilder->setParameter($parameterName . 'To', $value['to']);
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                    $queryBuilder->setParameter($parameterName, '%' . $value . '%');
                    break;
                default:
                    $queryBuilder->setParameter($parameterName, $value);
                    break;
            }
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