<?php

namespace Draw\Bundle\SonataExtraBundle\PreventDelete;

class RelationsDumper
{
    /**
     * @param array<PreventDelete> $relations
     */
    public function xmlDump(array $relations): string
    {
        usort(
            $relations,
            static fn (PreventDelete $a, PreventDelete $b): int => $a->getClass() <=> $b->getClass()
                ?: $a->getRelatedClass() <=> $b->getRelatedClass()
                    ?: $a->getPath() <=> $b->getPath()
        );

        $domDoc = new \DOMDocument('1.0', 'UTF-8');

        $domDoc->formatOutput = true;

        $root = $domDoc->createElement('relations');

        foreach ($relations as $relation) {
            $relationElement = $domDoc->createElement('relation');

            if ($relation->getClass()) {
                $relationElement->setAttribute('class', $relation->getClass());
            }

            if ($relation->getRelatedClass()) {
                $relationElement->setAttribute('related-class', $relation->getRelatedClass());
            }

            if ($relation->getPath()) {
                $relationElement->setAttribute('path', $relation->getPath());
            }

            if (!$relation->getPreventDelete()) {
                $relationElement->setAttribute('prevent-delete', 'false');
            }

            $metadata = $relation->getMetadata();

            ksort($metadata);

            foreach ($metadata as $key => $value) {
                $metaElement = $domDoc->createElement('metadata');
                $metaElement->setAttribute('key', $key);
                $metaElement->setAttribute('value', (string) $value);
                $relationElement->appendChild($metaElement);
            }

            $root->appendChild($relationElement);
        }

        $domDoc->appendChild($root);

        return $domDoc->saveXML();
    }
}
