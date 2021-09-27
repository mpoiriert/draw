<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Schema\BaseParameter;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotNull as SupportedConstraint;

class NotNullConstraintExtractor extends ConstraintExtractor
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

        if ($context->validationConfiguration instanceof BaseParameter) {
            $context->validationConfiguration->required = true;

            return;
        }

        if (!$context->classSchema->required || !in_array($context->propertyName, $context->classSchema->required)) {
            $context->classSchema->required[] = $context->propertyName;
        }
    }
}
