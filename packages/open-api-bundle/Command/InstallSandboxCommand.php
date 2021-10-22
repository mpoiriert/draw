<?php

namespace Draw\Bundle\OpenApiBundle\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use ZipArchive;

class InstallSandboxCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('draw:open-api:install-sandbox')
            ->addOption('tag', null, InputOption::VALUE_REQUIRED, 'Swagger UI tag to install (eg. "v3.52.5")', 'master');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $zipPath = $this->downloadZip($output, $input->getOption('tag'));
        $this->extractZip(
            $output,
            $zipPath,
            dirname(__DIR__).'/Resources/public/sandbox'
        );

        return 0;
    }

    private function downloadZip(OutputInterface $output, string $tag): string
    {
        $output->write('Downloading Swagger UI...');
        $path = (string) tempnam(sys_get_temp_dir(), 'swagger-ui-'.$tag.'.zip');
        if (!file_put_contents($path, file_get_contents('https://github.com/swagger-api/swagger-ui/archive/'.$tag.'.zip'))) {
            throw new RuntimeException(sprintf('Unable to write Swagger UI ZIP archive to "%s".', $path));
        }

        $output->writeln(' Ok.');

        return $path;
    }

    private function extractZip(OutputInterface $output, string $zipPath, string $outputPath): void
    {
        $output->write('Extracting zip file...');

        $zip = new ZipArchive();
        if (false === $zip->open($zipPath)) {
            throw new RuntimeException(sprintf('Cannot open zip file "%s".', $zipPath));
        }

        $fileSystem = new Filesystem();

        if (is_dir($outputPath)) {
            $fileSystem->remove($outputPath);
        }

        $fileSystem->mkdir($outputPath);

        for ($i = 0; $i < $zip->numFiles; ++$i) {
            $filename = $zip->getNameIndex($i);
            $zipFile = sprintf('zip://%s#%s', $zipPath, $filename);
            // Remove the first directory (eg. "wysiwyg-editor-master") from the file path
            $explodedPath = explode('/', $filename, 2);
            $realFilePath = $explodedPath[1];
            if (0 !== strpos($realFilePath, 'dist/')) {
                continue;
            }

            if ('/' === substr($filename, -1)) {
                $fileSystem->mkdir($outputPath.'/'.$realFilePath);
            } else {
                copy($zipFile, $outputPath.'/'.substr($realFilePath, strlen('dist/')));
            }
        }
        $zip->close();

        $output->writeln(' Ok.');
    }
}
