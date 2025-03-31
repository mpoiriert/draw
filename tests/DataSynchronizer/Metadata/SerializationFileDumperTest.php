<?php

namespace App\Tests\DataSynchronizer\Metadata;

use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireParameter;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\DataSynchronizer\Metadata\SerializationFileDumper;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Yaml\Yaml;

/**
 * @internal
 */
class SerializationFileDumperTest extends KernelTestCase implements AutowiredInterface
{
    #[AutowireService]
    private SerializationFileDumper $serializationFileDumper;

    #[AutowireParameter('%draw.data_synchronizer.metadata_directory%')]
    private string $extractorDirectory;

    public function testGenerateAllSerializerFiles(): void
    {
        $previous = $this->loadExtractorConfiguration();

        $this->serializationFileDumper->generateAllSerializerFiles();

        static::assertSame(
            $previous,
            $this->loadExtractorConfiguration()
        );
    }

    private function loadExtractorConfiguration(): array
    {
        $configuration = [];
        foreach (glob($this->extractorDirectory.'/*.yaml') as $file) {
            $configuration[$file] = Yaml::parseFile($file);
        }

        return $configuration;
    }
}
