<?php

namespace App\Tests\TesterBundle\PHPUnit\Extension\SetUpAutoWire;

use App\Entity\User;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireServiceDouble;
use Draw\Bundle\TesterBundle\WebTestCase;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AsMock;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\DoctrineExtra\ORM\EntityHandler;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @internal
 */
class AutowireServiceDoubleTest extends WebTestCase implements AutowiredInterface
{
    #[AutowireClient]
    private KernelBrowser $client;

    #[AutowireServiceDouble]
    private EntityHandler&Stub $entityHandlerDouble;

    #[AutowireService]
    private EntityHandler $entityHandler;

    public function testInstanceOfEntityHandler(): void
    {
        static::assertSame(
            $this->entityHandlerDouble,
            $this->entityHandler
        );
    }

    #[AsMock('entityHandlerDouble')]
    public function testUsersAction(): void
    {
        \assert($this->entityHandlerDouble instanceof MockObject);

        $this->entityHandlerDouble
            ->expects(static::once())
            ->method('findAll')
            ->with(User::class)
            ->willReturn([])
            ->seal()
        ;

        $this->client
            ->request('GET', '/api/users')
        ;

        static::assertResponseIsSuccessful();
    }
}
