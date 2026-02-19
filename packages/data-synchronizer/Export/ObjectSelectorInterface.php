<?php

namespace Draw\Component\DataSynchronizer\Export;

use Draw\Component\DataSynchronizer\Metadata\EntitySynchronizationMetadata;

interface ObjectSelectorInterface
{
    public function select(EntitySynchronizationMetadata $extractionMetadata): ?array;
}
