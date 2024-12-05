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
        $this->referenceRepository->addReference(
            $this->buildReferenceName($class, $name),
            $object
        );
    }

    public function hasObjectReference(string $class, string $name): bool
    {
        return $this->referenceRepository->hasReference(
            $this->buildReferenceName($class, $name),
            $class
        );
    }

    public function getObjectReference(string $class, string $name): object
    {
        return $this->referenceRepository->getReference(
            $this->buildReferenceName($class, $name),
            $class
        );
    }

    public function setObjectReference(string $class, string $name, object $object): void
    {
        $this->referenceRepository->setReference(
            $this->buildReferenceName($class, $name),
            $object
        );
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

    private function buildReferenceName(string $class, string $name): string
    {
        return \sprintf('%s.%s', $class, $name);
    }
}
