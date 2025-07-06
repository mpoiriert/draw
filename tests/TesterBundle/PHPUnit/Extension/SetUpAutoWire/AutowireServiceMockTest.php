<?php

namespace App\Tests\TesterBundle\PHPUnit\Extension\SetUpAutoWire;

use App\Entity\User;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireServiceMock;
use Draw\Bundle\TesterBundle\WebTestCase;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\DoctrineExtra\ORM\EntityHandler;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

/**
 * @internal
 */
class AutowireServiceMockTest extends WebTestCase implements AutowiredInterface
{
    #[AutowireClient]
    private KernelBrowser $client;

    #[AutowireServiceMock]
    private EntityHandler&MockObject $entityHandlerMock;

    #[AutowireService]
    private EntityHandler $entityHandler;

    public function testInstanceOfEntityHandler(): void
    {
        static::assertSame(
            $this->entityHandlerMock,
            $this->entityHandler
        );
    }

    public function testUsersAction(): void
    {
        $this->entityHandlerMock
            ->expects(static::once())
            ->method('findAll')
            ->with(User::class)
            ->willReturn([])
        ;

        $this->client
            ->request('GET', '/api/users')
        ;

        static::assertResponseIsSuccessful();
    }
}
