<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireInterface;
use PHPUnit\Framework\TestCase;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class AutowireReloadedEntity implements AutowireInterface
{
    use KernelTestCaseAutowireDependentTrait;

    public static function getPriority(): int
    {
        return 0;
    }

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty): void
    {
        $entity = $reflectionProperty->getValue($testCase);

        if (null === $entity) {
            return;
        }

        $manager = $this->getContainer($testCase)
            ->get(ManagerRegistry::class)
            ->getManagerForClass($entity::class)
        ;

        $metadata = $manager->getClassMetadata($entity::class);
        $id = $metadata->getIdentifierValues($entity);

        $entity = $manager->find($entity::class, $id);

        $reflectionProperty->setValue(
            $testCase,
            $entity
        );
    }
}
