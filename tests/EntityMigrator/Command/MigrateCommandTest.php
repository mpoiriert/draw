<?php

namespace App\Tests\EntityMigrator\Command;

use App\Tests\FilteredCommandTestTrait;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\EntityMigrator\Command\MigrateCommand;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;

/**
 * @internal
 */
class MigrateCommandTest extends KernelTestCase implements AutowiredInterface
{
    use FilteredCommandTestTrait;

    #[AutowireService(MigrateCommand::class)]
    protected ?Command $command = null;

    public function getCommandName(): string
    {
        return 'draw:entity-migrator:migrate';
    }

    public static function provideTestArgument(): iterable
    {
        yield [
            'migration-name',
            null,
        ];
    }

    public static function provideTestOption(): iterable
    {
        return [];
    }

    public function testExecute(): void
    {
        $this->execute([
            'migration-name' => 'user-set-comment-null',
        ])
            ->test(
                CommandDataTester::create()
                    ->setExpectedDisplay(['[OK] Migration started'])
                    ->setExpectedErrorOutput(null)
            )
        ;
    }
}
