<?php

namespace Draw\Bundle\SonataExtraBundle\Security\Voter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Draw\DoctrineExtra\ORM\Query\CommentSqlWalker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class RelationPreventDeleteCanVoter implements VoterInterface
{
    /**
     * @param iterable<Relation> $relations
     */
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private iterable $relations
    ) {

    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (!$subject) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        foreach ($attributes as $attribute) {
            if ('SONATA_CAN_DELETE' !== $attribute) {
                return VoterInterface::ACCESS_ABSTAIN;
            }

            foreach ($this->relations as $relation) {
                $class = $relation->getClass();
                if (!$subject instanceof $class) {
                    continue;
                }

                $manager = $this->managerRegistry->getManagerForClass($relation->getRelatedClass());

                \assert($manager instanceof EntityManagerInterface);

                $paths = explode('.', $relation->getPath());

                $queryBuilder = $manager->createQueryBuilder()
                    ->from($relation->getRelatedClass(), 'root');

                foreach ($paths as $index => $path) {
                    $queryBuilder->innerJoin('root.'.$path, 'path_'.$index);
                }

                $identifiers = $manager->getClassMetadata($relation->getRelatedClass())->getIdentifier();

                $queryBuilder
                   ->andWhere('path_'.(\count($paths) - 1).' = :subject')
                   ->setParameter('subject', $subject);

                foreach ($identifiers as $identifier) {
                    $queryBuilder->addSelect('root.'.$identifier);
                }

                $query = $queryBuilder
                    ->setMaxResults(1)
                    ->getQuery();

                if (class_exists(CommentSqlWalker::class)) {
                    CommentSqlWalker::addComment(
                        $query,
                        'From Draw\Bundle\SonataExtraBundle\Security\Voter\RelationPreventDeleteCanVoter'
                    );

                    CommentSqlWalker::addComment(
                        $query,
                        $relation->getRelatedClass().'.'.$relation->getPath()
                    );
                }

                $result = $query->getResult();

                if (\count($result) > 0) {
                    return VoterInterface::ACCESS_DENIED;
                }
            }
        }

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
