<?php

namespace Draw\Bundle\CronBundle\Tests\Command;

use Draw\Bundle\CronBundle\Command\DumpToFileCommand;
use Draw\Bundle\CronBundle\CronManager;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\Application\CommandTestCase;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class DumpToFileCommandTest extends CommandTestCase
{
    /**
     * @var ObjectProphecy
     */
    private $cronManagerProphecy;

    public function createCommand(): Command
    {
        $this->cronManagerProphecy = $this->prophesize(CronManager::class);

        return new DumpToFileCommand($this->cronManagerProphecy->reveal(CronManager::class));
    }

    public function getCommandName(): string
    {
        return 'draw:cron:dump-to-file';
    }

    public function getCommandDescription(): string
    {
        return 'Dump the cron job configuration to a file compatible with crontab.';
    }

    public function provideTestArgument(): iterable
    {
        yield ['filePath', InputArgument::REQUIRED, 'The file path where to dump.'];
    }

    public function provideTestOption(): iterable
    {
        yield ['override', null, InputOption::VALUE_NONE, 'If the file is present we override it.'];
    }

    public function testExecuteNewFile()
    {
        $this->cronManagerProphecy
            ->__call('dumpJobs', [])
            ->shouldBeCalledOnce()
            ->willReturn('Output');

        $filePath = sys_get_temp_dir().'/'.uniqid().'.txt';
        register_shutdown_function('unlink', $filePath);
        $this
            ->execute(['filePath' => $filePath])
            ->test(CommandDataTester::create());

        $this->assertSame('Output', file_get_contents($filePath));
    }

    public function testExecuteNewFileOverride()
    {
        $this->cronManagerProphecy
            ->__call('dumpJobs', [])
            ->shouldBeCalledOnce()
            ->willReturn('Output');

        $filePath = sys_get_temp_dir().'/'.uniqid().'.txt';
        file_put_contents($filePath, 'Before');
        register_shutdown_function('unlink', $filePath);
        $this
            ->execute(['filePath' => $filePath, '--override' => '1'])
            ->test(CommandDataTester::create());

        $this->assertSame('Output', file_get_contents($filePath));
    }

    public function testExecuteNewFile_noOverrideException()
    {
        $filePath = sys_get_temp_dir().'/'.uniqid().'.txt';
        file_put_contents($filePath, 'Before');
        register_shutdown_function('unlink', $filePath);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage(sprintf(
            'The file [%s] already exists. Remove the file or use option --override.',
            $filePath
        ));

        $this->execute(['filePath' => $filePath]);
    }
}
