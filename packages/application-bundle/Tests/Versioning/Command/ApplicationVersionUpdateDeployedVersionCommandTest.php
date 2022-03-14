<?php

namespace Draw\Bundle\ApplicationBundle\Tests\Versioning\Command;

use Draw\Bundle\ApplicationBundle\Tests\AppKernel;
use Draw\Bundle\ApplicationBundle\Versioning\Command\ApplicationVersionUpdateDeployedVersionCommand;
use Draw\Bundle\ApplicationBundle\Versioning\VersionManager;
use Draw\Bundle\TesterBundle\DependencyInjection\ServiceTesterTrait;
use Draw\Component\Tester\Application\CommandTestTrait;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * @covers \Draw\Bundle\ApplicationBundle\Versioning\Command\ApplicationVersionUpdateDeployedVersionCommand
 */
class ApplicationVersionUpdateDeployedVersionCommandTest extends KernelTestCase
{
    use CommandTestTrait;
    use ServiceTesterTrait;

    /**
     * @var MockObject
     */
    private $versionManager;

    // For symfony 4.x
    protected static $class = AppKernel::class;

    protected static function getKernelClass(): string
    {
        return AppKernel::class;
    }

    public function createCommand(): Command
    {
        static::shutdownMainTestContainer();

        $this->versionManager = $this->createMock(VersionManager::class);

        static::getMainTestContainer()->set(VersionManager::class, $this->versionManager);

        return static::getService(ApplicationVersionUpdateDeployedVersionCommand::class);
    }

    public function getCommandName(): string
    {
        return 'draw:application:update-deployed-version';
    }

    public function getCommandDescription(): string
    {
        return 'You should run this after every successful application deployment.';
    }

    public function provideTestArgument(): iterable
    {
        return [];
    }

    public function provideTestOption(): iterable
    {
        return [];
    }

    public function testExecute(): void
    {
        $this->versionManager->expects($this->once())->method('updateDeployedVersion');

        $this->execute([])
            ->path('statusCode')->assertSame(0);
    }
}
