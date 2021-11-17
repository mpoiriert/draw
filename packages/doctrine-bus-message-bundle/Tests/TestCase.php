<?php

namespace Draw\Bundle\DoctrineBusMessageBundle\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Draw\Bundle\TesterBundle\DependencyInjection\ServiceTesterTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TestCase extends KernelTestCase
{
    use ServiceTesterTrait;

    protected static $class = AppKernel::class;

    public static function loadDatabase(): void
    {
        $entityManager = static::getService(EntityManagerInterface::class);

        // Run the schema update tool using our entity metadata
        (new SchemaTool($entityManager))
            ->updateSchema($entityManager->getMetadataFactory()->getAllMetadata());
    }
}
