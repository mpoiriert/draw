<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Range;

class RangeConstraintExtractor extends BaseConstraintExtractor
{
    public function supportConstraint(Constraint $constraint): bool
    {
        return $constraint instanceof Range;
    }

    /**
     * @param Range&Constraint $constraint
     */
    public function extractConstraint(Constraint $constraint, ConstraintExtractionContext $context): void
    {
        $this->assertSupportConstraint($constraint);

        $context->validationConfiguration->maximum = $constraint->max;
        $context->validationConfiguration->minimum = $constraint->min;
    }
}
