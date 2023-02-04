<?php

namespace Draw\Component\Core\FilterExpression\Expression;

use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

class ConstraintExpressionEvaluator extends ExpressionEvaluator
{
    private PropertyAccessorInterface $propertyAccessor;

    private ValidatorInterface $validator;

    public function __construct(?PropertyAccessor $propertyAccessor = null, ?ValidatorInterface $validator = null)
    {
        $this->validator = $validator ?: (new ValidatorBuilder())->getValidator();
        $this->propertyAccessor = $propertyAccessor ?: PropertyAccess::createPropertyAccessorBuilder()
            ->enableExceptionOnInvalidPropertyPath()
            ->enableExceptionOnInvalidIndex()
            ->getPropertyAccessor();
    }

    public function evaluate($data, Expression $expression): bool
    {
        if (!$expression instanceof ConstraintExpression) {
            throw new \InvalidArgumentException('Expression of class ['.$expression::class.'] is not supported');
        }

        $value = $data;
        if (null !== $expression->getPath()) {
            if (!$this->propertyAccessor->isReadable($data, $expression->getPath())) {
                return false;
            }

            $value = $this->propertyAccessor->getValue($data, $expression->getPath());
        }

        $constraintViolationList = $this->validator->validate(
            $value,
            $expression->getConstraints(),
            $expression->getGroups()
        );

        return !\count($constraintViolationList);
    }
}
