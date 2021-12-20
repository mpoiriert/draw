<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count as SupportedConstraint;

class CountConstraintExtractor extends ConstraintExtractor
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

        if ($constraint->min) {
            $context->validationConfiguration->minItems = $constraint->min;
        }

        if ($constraint->max) {
            $context->validationConfiguration->maxItems = $constraint->max;
        }
    }
}
