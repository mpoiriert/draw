<?php

namespace Draw\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RemoteFileExistsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof RemoteFileExists) {
            throw new UnexpectedTypeException($constraint, RemoteFileExists::class);
        }

        if (!$this->remoteFileExists($value)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation()
            ;
        }
    }

    public function remoteFileExists(string $url): bool
    {
        $ch = curl_init($url);

        curl_setopt($ch, \CURLOPT_NOBODY, true);
        curl_setopt($ch, \CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, \CURLOPT_CONNECTTIMEOUT, 3);
        curl_setopt($ch, \CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, \CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, \CURLOPT_MAXREDIRS, 3);

        curl_exec($ch);

        $httpCode = curl_getinfo($ch, \CURLINFO_HTTP_CODE);

        unset($ch);

        return $httpCode >= 200 && $httpCode < 300;
    }
}
