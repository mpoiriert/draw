<?php

namespace Draw\Component\OpenApi\Exception;

use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidatorException;

class ConstraintViolationListException extends ValidatorException
{
    private ConstraintViolationList $violationList;

    public function __construct(ConstraintViolationList $violationList)
    {
        $this->violationList = $violationList;
        parent::__construct((string) $violationList);
    }

    public function getViolationList(): ConstraintViolationList
    {
        return $this->violationList;
    }
}
