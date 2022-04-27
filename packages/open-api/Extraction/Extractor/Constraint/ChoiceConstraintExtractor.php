<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;

class ChoiceConstraintExtractor extends BaseConstraintExtractor
{
    public function supportConstraint(Constraint $constraint): bool
    {
        return $constraint instanceof Choice;
    }

    /**
     * @param Choice|Constraint $constraint
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
