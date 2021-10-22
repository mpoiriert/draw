<?php

namespace Draw\Bundle\OpenApiBundle\Exception;

use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Exception\ValidatorException;

class ConstraintViolationListException extends ValidatorException
{
    /**
     * @var ConstraintViolationListInterface
     */
    private $violationList;

    public function setViolationList(ConstraintViolationListInterface $violationList)
    {
        $this->violationList = $violationList;
        $this->message = (string) $violationList;
    }

    public function getViolationList()
    {
        return $this->violationList;
    }
}
