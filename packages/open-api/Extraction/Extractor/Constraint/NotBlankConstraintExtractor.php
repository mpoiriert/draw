<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Schema\BaseParameter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;

class NotBlankConstraintExtractor extends BaseConstraintExtractor
{
    public function supportConstraint(Constraint $constraint): bool
    {
        return $constraint instanceof NotBlank;
    }

    /**
     * @param Constraint|NotBlank $constraint
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
