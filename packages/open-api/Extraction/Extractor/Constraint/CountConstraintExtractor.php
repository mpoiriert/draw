<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Count;

class CountConstraintExtractor extends BaseConstraintExtractor
{
    public function supportConstraint(Constraint $constraint): bool
    {
        return $constraint instanceof Count;
    }

    /**
     * @param Count&Constraint $constraint
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
