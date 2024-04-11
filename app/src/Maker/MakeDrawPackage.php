<?php

namespace App\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\YamlSourceManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

class MakeDrawPackage extends AbstractMaker
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private string $kernelProjectDir,
        private Environment $environment
    ) {
    }

    public static function getCommandName(): string
    {
        return 'make:draw-package';
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
        $command
            ->addArgument('type', InputArgument::REQUIRED, 'The type of the package:: (component|bundle)')
            ->addArgument('name', InputArgument::REQUIRED, 'The name of the package: Example: my-package')
            ->addArgument('description', InputArgument::REQUIRED, 'The description of the package');
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $type = $input->getArgument('type');
        $packageName = $input->getArgument('name');

        if (!\in_array($type, ['component', 'bundle'])) {
            $io->error('Invalid package type');

            return;
        }

        if (empty($packageName)) {
            $io->error('Invalid package name');

            return;
        }

        if ('bundle' === $type) {
            if (!str_ends_with($packageName, '-bundle')) {
                $io->error('Package name should end with -bundle');

                return;
            }
        } else {
            if (str_ends_with($packageName, '-bundle')) {
                $io->error('Package name should not end with -bundle');

                return;
            }
        }

        $namespace = $this->getNamespace($packageName, $type);

        $generator
            ->dumpFile(
                'packages/'.$packageName.'/composer.json',
                $this->environment->render(
                    $this->environment->createTemplate(file_get_contents(__DIR__.'/../Resources/skeleton/draw-package/composer.json.twig')),
                    [
                        'packageName' => $packageName,
                        'packageDescription' => $input->getArgument('description'),
                        'namespace' => $namespace,
                        'type' => $type,
                    ]
                )
            );

        $generator
            ->dumpFile(
                'packages/'.$packageName.'/phpunit.xml.dist',
                file_get_contents(__DIR__.'/../Resources/skeleton/draw-package/phpunit.xml.dist')
            );

        $generator
            ->dumpFile(
                $this->kernelProjectDir.'/composer.json',
                $this->getNewComposerContents($packageName, $namespace)
            );

        $generator
            ->dumpFile(
                $this->kernelProjectDir.'/.github/workflows/after_splitting_test.yaml',
                $this->getNewGithubWorkflowsAfterSplittingTestContents($packageName)
            );

        $generator->writeChanges();
    }

    private function getNewComposerContents(string $packageName, string $namespace): string
    {
        $composer = json_decode(
            file_get_contents($this->kernelProjectDir.'/composer.json'),
            true
        );

        $composer['autoload']['psr-4'][$namespace.'\\'] = 'packages/'.$packageName.'/';

        ksort($composer['autoload']['psr-4']);

        $composer['replace']['draw/'.$packageName] = 'self.version';

        ksort($composer['replace']);

        return json_encode($composer, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_SLASHES);
    }

    private function getNewGithubWorkflowsAfterSplittingTestContents(string $packageName): string
    {
        $yamlSourceManipulator = new YamlSourceManipulator(
            file_get_contents($this->kernelProjectDir.'/.github/workflows/after_splitting_test.yaml')
        );

        $data = $yamlSourceManipulator->getData();

        // Sorting doesn't work with the current implementation of YamlSourceManipulator
        $data['jobs']['after_split_testing']['strategy']['matrix']['package_name'][] = $packageName;

        $yamlSourceManipulator->setData($data);

        return $yamlSourceManipulator->getContents();
    }

    private function getNamespace(string $packageName, string $type): string
    {
        $namespace = str_replace('-', ' ', $packageName);
        $namespace = ucwords($namespace);
        $namespace = str_replace(' ', '', $namespace);

        return sprintf(
            'Draw\%s\%s',
            'component' === $type ? 'Component' : 'Bundle',
            $namespace
        );
    }
}
