<?php

namespace Draw\DoctrineExtra\Common\DataFixtures;

use Doctrine\Common\DataFixtures\ReferenceRepository;

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
}
