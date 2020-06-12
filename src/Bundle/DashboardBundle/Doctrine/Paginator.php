<?php

namespace Draw\Bundle\DashboardBundle\Doctrine;

use JMS\Serializer\Annotation as Serializer;

/**
 * @Serializer\ExclusionPolicy("ALL")
 */
class Paginator
{
    /**
     * @var int
     *
     * @Serializer\Expose()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("pageSize")
     */
    private $pageSize = 5;

    /**
     * @var int[]
     *
     * @Serializer\Expose()
     * @Serializer\Type("array<int>")
     * @Serializer\SerializedName("pageSizeOptions")
     */
    private $pageSizeOptions = [5, 10, 25];

    /**
     * @var \Doctrine\ORM\Tools\Pagination\Paginator
     */
    private $paginator;

    public function __construct($query, int $pageSize = 5, ?array $pageSizeOptions = [5, 10, 25], $fetchJoinCollection = true)
    {
        if (null !== $pageSizeOptions) {
            $this->pageSizeOptions = $pageSizeOptions;
        }

        if (!$pageSize) {
            $pageSize = min($this->pageSizeOptions);
        } else {
            $pageSize = min($pageSize, max($this->pageSizeOptions));
        }

        $this->pageSize = $pageSize;
        $this->paginator = new \Doctrine\ORM\Tools\Pagination\Paginator($query, $fetchJoinCollection);
    }

    public function goToPage($pageIndex)
    {
        $this->paginator
            ->getQuery()
            ->setMaxResults($this->pageSize)
            ->setFirstResult($pageIndex * $this->pageSize);
    }

    public function getPageSize(): ?int
    {
        return $this->pageSize;
    }

    public function setPageSize(int $pageSize): void
    {
        $this->pageSize = $pageSize;
    }

    /**
     * @return int[]
     */
    public function getPageSizeOptions(): array
    {
        return $this->pageSizeOptions;
    }

    public function setPageSizeOptions(array $pageSizeOptions): void
    {
        $this->pageSizeOptions = $pageSizeOptions;
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Type("int")
     * @Serializer\SerializedName("totalCount")
     *
     * @return int
     */
    public function count()
    {
        return $this->paginator->count();
    }

    /**
     * @Serializer\VirtualProperty()
     * @Serializer\Type("array<generic>")
     *
     * @return array
     */
    public function getData()
    {
        return $this->paginator->getIterator()->getArrayCopy();
    }
}
