<?php

namespace Draw\Component\OpenApi\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InstallSandboxCommand extends Command
{
    private Filesystem $filesystem;

    public function __construct(?Filesystem $filesystem = null)
    {
        parent::__construct();
        $this->filesystem = $filesystem ?: new Filesystem();
    }

    protected function configure(): void
    {
        $this
            ->setName('draw:open-api:install-sandbox')
            ->addArgument('path', InputArgument::REQUIRED, 'Path were to extract the zip')
            ->setDescription('Install Open Api Sandbox from downloaded zip base on tag version.')
            ->addOption('tag', null, InputOption::VALUE_REQUIRED, 'Swagger UI tag to install (eg. "v3.52.5")', 'master');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $zipPath = $this->downloadZip($output, $input->getOption('tag'));
        $this->extractZip(
            $output,
            $zipPath,
            $input->getArgument('path')
        );

        return 0;
    }

    private function downloadZip(OutputInterface $output, string $tag): string
    {
        $output->write('Downloading Swagger UI...');
        $path = sys_get_temp_dir().'/swagger-ui-'.$tag.'.zip';
        $this->filesystem->dumpFile($path, file_get_contents('https://github.com/swagger-api/swagger-ui/archive/'.$tag.'.zip'));

        register_shutdown_function([$this->filesystem, 'remove'], [$path]);

        $output->writeln(' Ok.');

        return $path;
    }

    private function extractZip(OutputInterface $output, string $zipPath, string $outputPath): void
    {
        $output->write('Extracting zip file...');

        $zip = new \ZipArchive();
        if (true !== $error = $zip->open($zipPath)) {
            throw new \RuntimeException(sprintf('Cannot open zip file [%s]. Error code [%s].', $zipPath, $error ?? 'File does not exists'));
        }

        $this->filesystem->remove($outputPath);
        $this->filesystem->mkdir($outputPath);

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
                $this->filesystem->mkdir($outputPath.'/'.$realFilePath);
            } else {
                copy($zipFile, $outputPath.'/'.substr($realFilePath, \strlen('dist/')));
            }
        }
        $zip->close();

        unlink($zipPath);

        $output->writeln(' Ok.');
    }
}
