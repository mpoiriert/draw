<?php

namespace App\Tests\DoctrineExtra\ORM\Command;

use App\Tests\FilteredCommandTestTrait;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Component\Tester\Application\CommandDataTester;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\DoctrineExtra\ORM\Command\GenerateGraphSchemaCommand;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;

/**
 * @internal
 */
class GenerateGraphSchemaCommandTest extends KernelTestCase implements AutowiredInterface
{
    use FilteredCommandTestTrait;

    #[AutowireService(GenerateGraphSchemaCommand::class)]
    protected ?Command $command = null;

    public function getCommandName(): string
    {
        return 'draw:doctrine:generate-graph-schema';
    }

    public static function provideTestArgument(): iterable
    {
        yield [
            'context-name',
            InputArgument::OPTIONAL,
            'default',
        ];
    }

    public static function provideTestOption(): iterable
    {
        return [];
    }

    /**
     * This test is just to make sure the command still does run.
     *
     * It will fail when we update to doctrine orm 3.
     */
    public function testExecute(): void
    {
        $this->execute(['context-name' => 'user'])
            ->test(
                CommandDataTester::create()
                    ->setExpectedDisplay(null)
            )
        ;
    }
}
