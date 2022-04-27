<?php

namespace Draw\Component\OpenApi\Exception;

use Exception;

/**
 * Throws this exception from an extractor to stop extraction. This is useful for caching extraction.
 */
class ExtractionCompletedException extends Exception
{
}
