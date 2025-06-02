<?php

namespace App\Tests\SonataExtraBundle\PreventDelete;

use Draw\Bundle\SonataExtraBundle\PreventDelete\PreventDeleteRelationLoader;
use Draw\Bundle\SonataExtraBundle\PreventDelete\RelationsDumper;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class RelationsDumperTest extends KernelTestCase implements AutowiredInterface
{
    private static bool $resetFile = false;

    #[AutowireService]
    private PreventDeleteRelationLoader $preventDeleteRelationLoader;

    #[AutowireService]
    private RelationsDumper $relationsDumper;

    public function testXmlDump(): void
    {
        $relations = $this->preventDeleteRelationLoader->getRelations();

        $file = __DIR__.'/fixtures/PreventDeleteRelationLoaderTest/testGetRelations.xml';

        if (!file_exists($file) || self::$resetFile) {
            file_put_contents($file, $this->relationsDumper->xmlDump($relations));

            static::fail("The file [{$file}] does not exist or has been reset. Review it to ensure it contains the expected XML structure.");
        }

        static::assertXmlStringEqualsXmlFile(
            $file,
            $this->relationsDumper->xmlDump($relations)
        );
    }
}
