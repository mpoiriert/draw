<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count as SupportedConstraint;

class CountConstraintExtractor extends ConstraintExtractor
{
    /**
     * @return bool
     */
    public function supportConstraint(Constraint $constraint)
    {
        return $constraint instanceof SupportedConstraint;
    }

    /**
     * @param SupportedConstraint|Constraint $constraint
     */
    public function extractConstraint(Constraint $constraint, ConstraintExtractionContext $context)
    {
        $this->assertSupportConstraint($constraint);

        if ($constraint->min) {
            $context->propertySchema->minItems = $constraint->min;
        }

        if ($constraint->max) {
            $context->propertySchema->maxItems = $constraint->max;
        }
    }
}
