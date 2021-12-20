<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice as SupportedConstraint;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class ChoiceConstraintExtractor extends ConstraintExtractor
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

        if ($constraint->callback) {
            if (!is_callable($constraint->callback)) {
                throw new ConstraintDefinitionException('The Choice constraint expects a valid callback');
            }
            $choices = call_user_func($constraint->callback);
        } else {
            $choices = $constraint->choices;
        }

        foreach ($choices as $choice) {
            $context->validationConfiguration->enum[] = $choice;
        }
    }
}
