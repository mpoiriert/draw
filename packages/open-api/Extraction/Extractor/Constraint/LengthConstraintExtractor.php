<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;

class LengthConstraintExtractor extends BaseConstraintExtractor
{
    public function supportConstraint(Constraint $constraint): bool
    {
        return $constraint instanceof Length;
    }

    /**
     * @param Length&Constraint $constraint
     */
    public function extractConstraint(Constraint $constraint, ConstraintExtractionContext $context): void
    {
        $this->assertSupportConstraint($constraint);
        $context->validationConfiguration->maxLength = $constraint->max;
        $context->validationConfiguration->minLength = $constraint->min;
    }
}
