<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Symfony\Component\Validator\Constraint;

interface ConstraintExtractorInterface extends ExtractorInterface
{
    /**
     * @return bool
     */
    public function supportConstraint(Constraint $constraint);

    /**
     * Extract the constraint information.
     *
     * @return void
     */
    public function extractConstraint(Constraint $constraint, ConstraintExtractionContext $context);
}
