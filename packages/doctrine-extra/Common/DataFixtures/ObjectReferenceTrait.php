<?php

namespace Draw\DoctrineExtra\Common\DataFixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\Persistence\ObjectManager;

/**
 * @property ReferenceRepository $referenceRepository
 */
trait ObjectReferenceTrait
{
    public function addObjectReference(string $class, string $name, object $object): void
    {
        $this->referenceRepository->addReference($class.'.'.$name, $object);
    }

    public function hasObjectReference(string $class, string $name): bool
    {
        return $this->referenceRepository->hasReference($class.'.'.$name);
    }

    public function getObjectReference(string $class, string $name): object
    {
        return $this->referenceRepository->getReference($class.'.'.$name);
    }

    public function setObjectReference(string $class, string $name, object $object): void
    {
        $this->referenceRepository->setReference($class.'.'.$name, $object);
    }

    public function persistAndFlush(ObjectManager $objectManager, iterable $entities): void
    {
        foreach ($entities as $key => $entity) {
            $objectManager->persist($entity);

            if (!\is_int($key)) {
                $this->addObjectReference($entity::class, $key, $entity);
            }
        }

        $objectManager->flush();
    }
}
