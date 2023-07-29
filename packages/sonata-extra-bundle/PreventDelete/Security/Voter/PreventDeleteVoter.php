<?php

namespace Draw\Bundle\SonataExtraBundle\PreventDelete\Security\Voter;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDeleteRelationLoader;
use Draw\DoctrineExtra\ORM\Query\CommentSqlWalker;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class PreventDeleteVoter implements VoterInterface
{
    public function __construct(
        private ManagerRegistry $managerRegistry,
        private PreventDeleteRelationLoader $preventDeleteRelationLoader,
    ) {
    }

    public function vote(TokenInterface $token, $subject, array $attributes): int
    {
        if (!$subject) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        if (!\in_array('SONATA_CAN_DELETE', $attributes)) {
            return VoterInterface::ACCESS_ABSTAIN;
        }

        foreach ($this->preventDeleteRelationLoader->getRelations() as $relation) {
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

            $queryBuilder
                ->andWhere('path_'.(\count($paths) - 1).' = :subject')
                ->setParameter('subject', $subject)
                ->select('1');

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

        return VoterInterface::ACCESS_ABSTAIN;
    }
}
