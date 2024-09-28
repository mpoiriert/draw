<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire;

use Doctrine\Persistence\ManagerRegistry;
use Draw\Component\Core\Reflection\ReflectionExtractor;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Attribute\Exclude;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
#[Exclude]
class AutowireEntity implements AutowireInterface
{
    use KernelTestCaseAutowireDependentTrait;

    public function __construct(
        private array $criteria = [],
        private ?string $class = null,
    ) {
    }

    public static function getPriority(): int
    {
        return 0;
    }

    public function autowire(TestCase $testCase, \ReflectionProperty $reflectionProperty): void
    {
        $class = $this->class;

        if (null === $class) {
            $classes = ReflectionExtractor::getClasses($reflectionProperty->getType());

            if (1 !== \count($classes)) {
                throw new \RuntimeException('Property '.$reflectionProperty->getName().' of class '.$testCase::class.' must have a type hint.');
            }

            $class = $classes[0];
        }

        $entity = $this->getContainer($testCase)
            ->get(ManagerRegistry::class)
            ->getManager()
            ->getRepository($class)
            ->findOneBy($this->criteria);

        $reflectionProperty->setValue(
            $testCase,
            $entity
        );
    }
}
