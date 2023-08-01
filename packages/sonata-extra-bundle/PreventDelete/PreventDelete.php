<?php

namespace Draw\Bundle\SonataExtraBundle\PreventDelete;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Draw\DoctrineExtra\ORM\Query\CommentSqlWalker;

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class PreventDelete
{
    public function __construct(
        private ?string $class = null,
        private ?string $relatedClass = null,
        private ?string $path = null,
        private bool $preventDelete = true,
        private ?array $metadata = []
    ) {
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(?string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getRelatedClass(): ?string
    {
        return $this->relatedClass;
    }

    public function setRelatedClass(?string $relatedClass): static
    {
        $this->relatedClass = $relatedClass;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): static
    {
        $this->path = $path;

        return $this;
    }

    public function getPreventDelete(): bool
    {
        return $this->preventDelete;
    }

    public function setPreventDelete(bool $preventDelete): static
    {
        $this->preventDelete = $preventDelete;

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata ?? [];
    }

    public function exists(ManagerRegistry $managerRegistry, object $subject): bool
    {
        $query = $this->createQueryBuilder($managerRegistry, $subject)
            ->select('1')
            ->setMaxResults(1)
            ->getQuery();

        if (class_exists(CommentSqlWalker::class)) {
            CommentSqlWalker::addComment(
                $query,
                'From Draw\Bundle\SonataExtraBundle\Security\Voter\RelationPreventDeleteCanVoter'
            );

            CommentSqlWalker::addComment(
                $query,
                $this->getRelatedClass().'.'.$this->getPath()
            );
        }

        return \count($query->getResult()) > 0;
    }

    public function getEntities(ManagerRegistry $managerRegistry, object $subject, int $limit = 10): array
    {
        $ids = $this->createQueryBuilder($managerRegistry, $subject)
            ->select('DISTINCT(root)')
            ->setMaxResults($limit)
            ->getQuery()
            ->execute();

        if (!$ids) {
            return $ids;
        }

        $entityManager = $managerRegistry->getManagerForClass($this->getRelatedClass());

        \assert($entityManager instanceof EntityManagerInterface);

        $idField = $entityManager
            ->getClassMetadata($this->getRelatedClass())
            ->getIdentifierFieldNames()[0];

        return $entityManager
            ->createQueryBuilder()
            ->from($this->getRelatedClass(), 'root')
            ->select('root')
            ->andWhere('root.'.$idField.' IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->execute();
    }

    private function createQueryBuilder(ManagerRegistry $managerRegistry, object $subject): QueryBuilder
    {
        $entityManager = $managerRegistry->getManagerForClass($this->getRelatedClass());

        \assert($entityManager instanceof EntityManagerInterface);

        $paths = explode('.', $this->getPath());

        $queryBuilder = $entityManager->createQueryBuilder()
            ->from($this->getRelatedClass(), 'root');

        $nextAlias = 'root';
        foreach ($paths as $index => $path) {
            $queryBuilder->innerJoin($nextAlias.'.'.$path, 'path_'.$index);
            $nextAlias = 'path_'.$index;
        }

        $queryBuilder
            ->andWhere('path_'.(\count($paths) - 1).' = :subject')
            ->setParameter('subject', $subject);

        return $queryBuilder;
    }
}
