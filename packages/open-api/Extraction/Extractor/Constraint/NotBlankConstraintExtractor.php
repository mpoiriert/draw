<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Schema\BaseParameter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank as SupportedConstraint;

class NotBlankConstraintExtractor extends ConstraintExtractor
{
    public function supportConstraint(Constraint $constraint): bool
    {
        return $constraint instanceof SupportedConstraint;
    }

    /**
     * @param Constraint|SupportedConstraint $constraint
     */
    public function extractConstraint(Constraint $constraint, ConstraintExtractionContext $context): void
    {
        $this->assertSupportConstraint($constraint);
        if (!isset($context->validationConfiguration->format)) {
            $context->validationConfiguration->format = 'not empty';
        }

        if ($constraint->allowNull) {
            return;
        }

        if ($context->validationConfiguration instanceof BaseParameter) {
            $context->validationConfiguration->required = true;

            return;
        }

        if (!$context->classSchema->required || !in_array($context->propertyName, $context->classSchema->required)) {
            $context->classSchema->required[] = $context->propertyName;
        }
    }
}
