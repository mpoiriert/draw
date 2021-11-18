<?php

namespace Draw\Component\OpenApi\Extraction\Extractor\Constraint;

use Draw\Component\OpenApi\Extraction\ExtractorInterface;
use Symfony\Component\Validator\Constraint;

interface ConstraintExtractorInterface extends ExtractorInterface
{
    public function supportConstraint(Constraint $constraint): bool;

    /**
     * Extract the constraint information.
     */
    public function extractConstraint(Constraint $constraint, ConstraintExtractionContext $context): void;
}
