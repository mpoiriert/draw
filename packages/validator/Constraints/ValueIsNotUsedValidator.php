<?php

namespace Draw\Component\Validator\Constraints;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class ValueIsNotUsedValidator extends ConstraintValidator
{
    public function __construct(private ManagerRegistry $managerRegistry)
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof ValueIsNotUsed) {
            throw new UnexpectedTypeException($constraint, ValueIsNotUsed::class);
        }

        if (null === $value) {
            return;
        }

        $manager = $this->managerRegistry->getManagerForClass($constraint->entityClass);

        \assert($manager instanceof EntityManagerInterface);
        $queryBuilder = $manager->createQueryBuilder()
            ->from($constraint->entityClass, 'root')
            ->andWhere('root.'.$constraint->field.' = :value')
            ->setParameter('value', $value);

        $identifiers = $manager->getClassMetadata($constraint->entityClass)->getIdentifier();

        foreach ($identifiers as $identifier) {
            $queryBuilder->addSelect('root.'.$identifier);
        }

        $result = $queryBuilder
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();

        if (0 === \count($result)) {
            return;
        }

        $this->context
            ->buildViolation($constraint->message)
            ->setParameter('{{ value }}', $value)
            ->setCode(ValueIsNotUsed::CODE)
            ->addViolation();
    }
}
