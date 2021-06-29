<?php

namespace Draw\Component\Core\FilterExpression\Expression;

use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ValidatorBuilder;

class ConstraintExpressionEvaluator extends ExpressionEvaluator
{
    /**
     * @var PropertyAccessorInterface
     */
    private $propertyAccessor;

    /**
     * @var ValidatorInterface
     */
    private $validator;

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
            throw new RuntimeException('Expression of class [' . get_class($expression) . '] is not supported');
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

        return !count($constraintViolationList);
    }
}