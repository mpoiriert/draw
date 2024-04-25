<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\DeleteTemporaryEntity;

use Doctrine\ORM\EntityManagerInterface;

class BaseTemporaryEntityCleaner implements TemporaryEntityCleanerInterface
{
    public static array $temporaryEntities = [];

    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function deleteTemporaryEntities(): void
    {
        foreach (self::$temporaryEntities as $index => $object) {
            $class = $object::class;
            $metadata = $this->entityManager->getClassMetadata($class);
            $id = $metadata->getIdentifierValues($object);

            if (empty($id)) {
                // This entity has no identifier, we can't delete it.
                // It can happen if we delete the entity manually in a test and use this flow as a fallback.
                continue;
            }

            if ($object = $this->entityManager->find($class, $metadata->getIdentifierValues($object))) {
                $this->entityManager->remove($object);
            }

            unset(self::$temporaryEntities[$index]);
        }

        $this->entityManager->flush();
    }
}
