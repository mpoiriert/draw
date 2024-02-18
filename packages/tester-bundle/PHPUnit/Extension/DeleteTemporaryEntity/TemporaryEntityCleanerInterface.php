<?php

namespace Draw\Bundle\TesterBundle\PHPUnit\Extension\DeleteTemporaryEntity;

interface TemporaryEntityCleanerInterface
{
    public function deleteTemporaryEntities(): void;
}
