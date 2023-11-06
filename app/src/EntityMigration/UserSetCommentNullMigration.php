<?php

namespace App\EntityMigration;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
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

    public function createQueryBuilder(): QueryBuilder
    {
        $manager = $this->managerRegistry->getManagerForClass(User::class);
        \assert($manager instanceof EntityManagerInterface);

        return $manager
            ->createQueryBuilder()
            ->from(User::class, 'user')
            ->select('user.id as id')
            ->where('user.comment != :comment')
            ->setParameter('comment', '');
    }
}
