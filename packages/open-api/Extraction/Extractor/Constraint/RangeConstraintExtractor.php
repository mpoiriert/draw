<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Range as SupportedConstraint;

class RangeConstraintExtractor extends ConstraintExtractor
{
    public function supportConstraint(Constraint $constraint): bool
    {
        return $constraint instanceof SupportedConstraint;
    }

    /**
     * @param SupportedConstraint|Constraint $constraint
     */
    public function extractConstraint(Constraint $constraint, ConstraintExtractionContext $context): void
    {
        $this->assertSupportConstraint($constraint);
        $context->validationConfiguration->maximum = $constraint->max;
        $context->validationConfiguration->minimum = $constraint->min;
    }
}
