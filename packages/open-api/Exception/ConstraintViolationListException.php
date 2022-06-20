<?php

namespace Draw\Component\OpenApi\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

class ConstraintViolationListException extends ValidatorException
{
    private ConstraintViolationListInterface $violationList;

    public function __construct(ConstraintViolationListInterface $violationList)
    {
        $this->violationList = $violationList;
        parent::__construct(method_exists($violationList, '__toString') ? $violationList->__toString() : '');
    }

    public function getViolationList(): ConstraintViolationListInterface
    {
        return $this->violationList;
    }
}
