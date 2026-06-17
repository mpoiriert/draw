<?php

namespace App\Tests\DataSynchronizer\Export;

use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\DataSynchronizer\Export\DataExporter;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @internal
 */
class DataExporterTest extends KernelTestCase implements AutowiredInterface
{
    private bool $writeFile = false;

    #[AutowireService]
    private DataExporter $dataExporter;

    public function testExport(): void
    {
        $directory = $this->extractZipFile($this->dataExporter->export()).'/';

        $files = [
            'files.json',
            'data/App_Entity_Tag.json',
            'data/App_Entity_TagTranslation.json',
        ];

        if ($this->writeFile) {
            foreach ($files as $file) {
                $content = file_get_contents($directory.$file);
                file_put_contents(
                    __DIR__.'/fixtures/DataExporterTest/testExport/'.$file,
                    $content
                );
            }
        }

        foreach ($files as $file) {
            static::assertJsonFileEqualsJsonFile(
                __DIR__.'/fixtures/DataExporterTest/testExport/'.$file,
                $directory.$file
            );
        }

        static::assertFalse(
            $this->writeFile,
            'Files were written to fixtures directory. Please check if they are correct.'
        );
    }

    private function extractZipFile(string $filePath): string
    {
        register_shutdown_function('unlink', $filePath);
        mkdir($tempDir = sys_get_temp_dir().'/'.uniqid('test-dump'));
        register_shutdown_function('exec', "rm -rf {$tempDir}");

        $zip = new \ZipArchive();
        $zip->open($filePath);
        $zip->extractTo($tempDir);

        return $tempDir;
    }
}
