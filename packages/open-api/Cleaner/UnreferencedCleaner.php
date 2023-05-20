<?php

namespace Draw\Component\OpenApi\Cleaner;

use Draw\Component\OpenApi\Schema\Root;

class UnreferencedCleaner implements ReferenceCleanerInterface
{
    final public const VENDOR_DATA_KEEP = 'x-draw-open-api-keep';

    public function cleanReferences(Root $rootSchema): bool
    {
        $cleaned = false;
        foreach ($rootSchema->definitions as $name => $definitionSchema) {
            if ($definitionSchema->getVendorDataKey(static::VENDOR_DATA_KEEP)) {
                continue;
            }
            if ($rootSchema->hasSchemaReference($rootSchema, '#/definitions/'.$name)) {
                continue;
            }

            unset($rootSchema->definitions[$name]);

            $cleaned = true;
        }

        return $cleaned;
    }
}
