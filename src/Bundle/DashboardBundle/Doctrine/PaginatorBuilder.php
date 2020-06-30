<?php

namespace Draw\Bundle\DashboardBundle\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Draw\Bundle\DashboardBundle\Event\PaginatorBuilderBuildEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class PaginatorBuilder
{
    private $eventDispatcher;

    private $managerRegistry;

    private $filters = [];

    private $orderBy = [];

    private $fromClass;

    private $fetchJoinCollection = null;

    /**
     * In case of zero will fallback on the first page size options
     *
     * @var int
     */
    private $pageSize = 0;

    private $pageIndex = 0;

    private $pageSizeOptions = [10, 25, 50];

    public function __construct(ManagerRegistry $managerRegistry, EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->managerRegistry = $managerRegistry;
    }

    public function fromClass($class): self
    {
        $this->fromClass = $class;

        return clone $this;
    }

    public function getFromClass(): ?string
    {
        return $this->fromClass;
    }

    public function filters(array $filters): self
    {
        $this->filters = $filters;

        return clone $this;
    }

    public function orderBy(array $orderBy): self
    {
        $this->orderBy = $orderBy;

        return clone $this;
    }

    public function getOrderBy(): array
    {
        return $this->orderBy;
    }

    public function getFilters(): array
    {
        return $this->filters;
    }

    public function fetchJoinCollection(?bool $fetch): self
    {
        $this->fetchJoinCollection = $fetch;

        return clone $this;
    }

    public function getFetchJoinCollection(): ?bool
    {
        return $this->fetchJoinCollection;
    }

    public function pageIndex(int $pageIndex): self
    {
        $this->pageIndex = $pageIndex;

        return clone $this;
    }

    public function getPageIndex(): int
    {
        return $this->pageIndex;
    }

    public function pageSize(?int $pageSize): self
    {
        $this->pageSize = $pageSize;

        return clone $this;
    }

    public function getPageSize(): int
    {
        return $this->pageSize ?: $this->getPageSizeOptions()[0];
    }

    /**
     * @param array|int[] $pageSizeOptions
     */
    public function pageSizeOptions(array $pageSizeOptions): self
    {
        $this->pageSizeOptions = $pageSizeOptions ? $pageSizeOptions : $this->pageSizeOptions;

        return clone $this;
    }

    /**
     * @return array|int[]
     */
    public function getPageSizeOptions(): array
    {
        return $this->pageSizeOptions;
    }

    public function build(callable $queryBuilderCallback = null)
    {
        if (!$this->fromClass) {
            throw new \RuntimeException('You must define a class you want to paginate from');
        }

        $queryBuilder = $this->buildQueryBuilder(
            $this->fromClass,
            $this->orderBy,
            $this->filters
        );

        if ($queryBuilderCallback) {
            call_user_func($queryBuilderCallback, $queryBuilder);
        }

        $fetchJoinCollection = $this->fetchJoinCollection;
        if (null === $fetchJoinCollection) {
            // This prevent some default case when you do a custom query hydration that would trigger a error if no id
            // is defined. We could pass false at the parameter but we detect if it's possible when not specified.
            $fetchJoinCollection = count($queryBuilder->getAllAliases()) === 1;
        }

        $this->eventDispatcher->dispatch(new PaginatorBuilderBuildEvent($this, $queryBuilder));

        $paginator = new Paginator(
            $queryBuilder->getQuery(),
            $this->getPageSize(),
            $this->getPageSizeOptions(),
            $fetchJoinCollection
        );

        $paginator->goToPage($this->getPageIndex());

        return $paginator;
    }

    public function extractFromRequest(Request $request): self
    {
        return $this
            ->orderBy($request->query->get('orderBy', []))
            ->filters($request->query->get('filters', []))
            ->pageSize($request->query->getInt('pageSize', $this->getPageSize()))
            ->pageIndex($request->query->getInt('pageIndex'));
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
                throw new \RuntimeException(sprintf('Invalid filter id paths configuration. Dot separator should be use to separate joins. Key [%s] is not a association. Path [%s]',
                    $path, $filter['id']));
            }

            $whereString = '%s.%s %s :%s';

            $parameterName = $alias . '_' . $path;

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
            throw new \RuntimeException(sprintf('Invalid filter id paths configuration. Dot separator should be use to separate joins. Key [%s] is not a association. Path [%s]',
                $path, $filter['id']));
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
