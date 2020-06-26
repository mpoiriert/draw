<?php

namespace Draw\Bundle\DashboardBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Symfony\Component\HttpFoundation\Request;

class PaginatorBuilder
{
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function fromRequest(
        $class,
        Request $request,
        callable $queryBuilderCallback = null,
        $fetchJoinCollection = null
    ): Paginator {
        $queryBuilder = $this->buildQueryBuilder(
            $class,
            $request->query->get('orderBy', []),
            $request->query->get('filters', [])
        );

        if ($queryBuilderCallback) {
            call_user_func($queryBuilderCallback, $queryBuilder);
        }

        if (null === $fetchJoinCollection) {
            // This prevent some default case when you do a custom query hydration that would trigger a error if no id
            // is defined. We could pass false at the parameter but we detect if it's possible when not specified.
            $fetchJoinCollection = count($queryBuilder->getAllAliases()) > 1;
        }

        $paginator = new Paginator(
            $queryBuilder->getQuery(),
            $request->query->getInt('pageSize'),
            null,
            $fetchJoinCollection
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
            if ('' === $value) {
                continue;
            }

            $paths = explode('.', $filter['id']);
            $comparison = $filter['comparison'];

            $path = array_shift($paths);

            if ($classMetadata->hasAssociation($path)) {
                $this->buildAssociationFilter(
                    $queryBuilder,
                    $alias,
                    $classMetadata,
                    $path,
                    array_merge(
                        $filter,
                        ['id' => (count($paths) ? implode('.', $paths) : '')]
                    )
                );
                continue;
            }

            if (count($paths)) {
                throw new \RuntimeException(sprintf('Invalid filter id paths configuration. Dot separator should be use to separate joins. Key [%s] is not a association. Path [%s]', $path, $filter['id']));
            }

            $whereString = '%s.%s %s :%s';

            $parameterName = $alias.'_'.$path;

            switch (true) {
                case 'BETWEEN' === $comparison:
                    $value = array_filter($value, function ($value) {
                        return '' !== $value;
                    });

                    if (!$value) {
                        return;
                    }

                    if (1 === count($value)) {
                        $this->addFilters(
                            $queryBuilder,
                            [
                                [
                                    'id' => $path,
                                    'comparison' => 'from' === key($value) ? '>=' : '<=',
                                    'value' => current($value),
                                ],
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
                            $path,
                            $parameterName,
                            $parameterName
                        ));
                    break;

                /* @noinspection PhpMissingBreakStatementInspection */
                case is_array($value):
                    $whereString = '%s.%s %s (:%s)';
                // no break
                default:
                    $queryBuilder
                        ->andWhere(sprintf(
                            $whereString,
                            $alias,
                            $path,
                            $comparison,
                            $parameterName
                        ));
                    break;
            }

            switch ($comparison) {
                case 'BETWEEN':
                    $queryBuilder->setParameter($parameterName.'From', $value['from']);
                    $queryBuilder->setParameter($parameterName.'To', $value['to']);
                    break;
                case 'LIKE':
                case 'NOT LIKE':
                    $queryBuilder->setParameter($parameterName, '%'.$value.'%');
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
        $associationName,
        array $filter
    ) {
        $associationClassMetadata = $this->managerRegistry
            ->getManagerForClass($classMetadata->getName())
            ->getClassMetadata($classMetadata->getAssociationTargetClass($associationName));

        $joinAlias = $associationName;

        if (!in_array($joinAlias, $queryBuilder->getAllAliases())) {
            $queryBuilder->join(sprintf('%s.%s', $alias, $associationName), $joinAlias);
        }

        $paths = array_filter(explode('.', $filter['id']));
        $path = array_shift($paths);
        if ($path) {
            if ($associationClassMetadata->hasAssociation($path)) {
                $this->buildAssociationFilter(
                    $queryBuilder,
                    $joinAlias,
                    $associationClassMetadata,
                    $path,
                    array_merge(
                        $filter,
                        ['id' => (count($paths) ? implode('.', $paths) : '')]
                    )
                );

                return;
            }
        }

        if (count($paths)) {
            throw new \RuntimeException(sprintf('Invalid filter id paths configuration. Dot separator should be use to separate joins. Key [%s] is not a association. Path [%s]', $path, $filter['id']));
        }

        if (is_null($path)) {
            $searchField = $associationClassMetadata->getIdentifierFieldNames()[0];
        } else {
            $searchField = $path;
        }

        $value = $filter['value'];
        switch (true) {
            case !is_array($value):
                break;
            case array_key_exists($searchField, $value):
                $value = $value[$searchField];
                break;
            default:
                $value = array_map(
                    function ($entry) use ($searchField) {
                        return $entry[$searchField];
                    },
                    $value
                );
                break;
        }

        $this->addFilters(
            $queryBuilder,
            [
                [
                    'id' => $searchField,
                    'value' => $value,
                    'comparison' => $filter['comparison'],
                ],
            ],
            $associationClassMetadata->getName(),
            $joinAlias
        );
    }
}
