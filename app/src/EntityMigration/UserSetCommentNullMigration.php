<?php

namespace App\EntityMigration;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\EntityMigrator\MigrationInterface;
use Draw\Component\EntityMigrator\MigrationTargetEntityInterface;

/**
 * @template-implements  MigrationInterface<User>
 */
class UserSetCommentNullMigration implements MigrationInterface
{
    public static function getName(): string
    {
        return 'user-set-comment-null';
    }

    public function __construct(private ManagerRegistry $managerRegistry)
    {

    }

    public static function getTargetEntityClass(): string
    {
        return User::class;
    }

    public function migrate(MigrationTargetEntityInterface $entity): void
    {
        $entity->setComment('');
    }

    public function needMigration(MigrationTargetEntityInterface $entity): bool
    {
        return '' !== $entity->getComment();
    }

    public function findAllThatNeedMigration(): iterable
    {
        $manager = $this->managerRegistry->getManagerForClass(User::class);
        \assert($manager instanceof EntityManagerInterface);

        $query = $manager
            ->createQuery('SELECT user.id FROM '.User::class.' user WHERE user.comment != :comment');

        foreach ($query->toIterable(['comment' => ''], $query::HYDRATE_SCALAR) as $userId) {
            yield $manager->getReference(User::class, $userId['id']);
        }
    }

    public function countAllThatNeedMigration(): ?int
    {
        $manager = $this->managerRegistry->getManagerForClass(User::class);
        \assert($manager instanceof EntityManagerInterface);

        return (int) $manager
            ->createQuery('SELECT count(user) FROM '.User::class.' user WHERE user.comment != :comment')
            ->setParameter('comment', '')
            ->getSingleScalarResult();
    }

    public function migrationIsCompleted(): bool
    {
        $repository = $this->managerRegistry->getRepository(User::class);
        \assert($repository instanceof EntityRepository);

        return null === $repository
            ->createQueryBuilder('user')
            ->select('user.id')
            ->where('user.comment != ""')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
