<?php

namespace Draw\Component\OpenApi\Cleaner;

use Draw\Component\OpenApi\Extraction\Extractor\Doctrine\InheritanceExtractor;
use Draw\Component\OpenApi\Schema\Root;

class DoctrineInheritanceCleaner implements ReferenceCleanerInterface
{
    public function cleanReferences(Root $rootSchema): bool
    {
        $cleaned = $this->removeUnReferenceRootEntity($rootSchema);

        foreach ($rootSchema->definitions as $name => $definitionSchema) {
            $rootEntityClass = $definitionSchema->getVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_ROOT_ENTITY_CLASS);

            if (!$rootEntityClass) {
                continue;
            }

            if ($this->hasDoctrineClass($rootSchema, $rootEntityClass)) {
                continue;
            }

            unset($rootSchema->definitions[$name]);

            $cleaned = true;
        }

        return $cleaned;
    }

    private function removeUnReferenceRootEntity(Root $rootSchema): bool
    {
        $cleaned = false;
        do {
            $suppressionOccurred = false;
            foreach ($rootSchema->definitions as $name => $definitionSchema) {
                if (!$definitionSchema->getVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_IS_ROOT_ENTITY)) {
                    continue;
                }

                if (!$rootSchema->hasSchemaReference($rootSchema, '#/definitions/'.$name)) {
                    unset($rootSchema->definitions[$name]);
                    $suppressionOccurred = true;
                    $cleaned = true;
                }
            }
        } while ($suppressionOccurred);

        return $cleaned;
    }

    private function hasDoctrineClass(Root $schema, string $className): bool
    {
        foreach ($schema->definitions as $definition) {
            $class = $definition->getVendorDataKey(InheritanceExtractor::VENDOR_DATA_DOCTRINE_ENTITY_CLASS);

            if ($class === $className) {
                return true;
            }
        }

        return false;
    }
}
