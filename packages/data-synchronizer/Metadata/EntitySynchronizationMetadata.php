<?php

namespace Draw\Component\DataSynchronizer\Metadata;

use Doctrine\ORM\Mapping\ClassMetadata;

#[\Attribute(\Attribute::TARGET_CLASS)]
class EntitySynchronizationMetadata
{
    public function __construct(
        /**
         * @var string[]
         */
        public array $lookUpFields = ['id'],
        public bool $purge = true,
        public array $excludeFields = [],
        public array $lateProcessFields = [],
        public ?string $exportExpression = null,
        public ?ClassMetadata $classMetadata = null,
    ) {
    }
}
