<?php

namespace App\Tests\TesterBundle\PHPUnit\Extension\SetUpAutoWire;

use App\EntityMigration\UserSetCommentNullMigration;
use Doctrine\Persistence\ManagerRegistry;
use Draw\Bundle\TesterBundle\Messenger\TransportTester;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireClient;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireParameter;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireService;
use Draw\Bundle\TesterBundle\PHPUnit\Extension\SetUpAutowire\AutowireTransportTester;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowiredInterface;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireDouble;
use Draw\Component\Tester\PHPUnit\Extension\SetUpAutowire\AutowireDoubleProperty;
use PHPUnit\Framework\MockObject\Stub;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 */
class SetUpAutowireExtensionTest extends WebTestCase implements AutowiredInterface
{
    #[
        AutowireService,
        AutowireDoubleProperty('managerRegistry')
    ]
    private UserSetCommentNullMigration $userSetCommentNullMigration;

    #[AutowireDouble]
    private ManagerRegistry&Stub $managerRegistry;

    #[AutowireClient]
    private KernelBrowser $client;

    #[AutowireTransportTester('sync')]
    private TransportTester $transportTester;

    #[AutowireParameter('%kernel.environment%_resolved')]
    private string $parameter;

    public function testAutowireService(): void
    {
        static::assertSame(
            static::getContainer()->get(UserSetCommentNullMigration::class),
            $this->userSetCommentNullMigration
        );
    }

    public function testAutowiredClient(): void
    {
        static::assertSame(
            static::getClient(),
            $this->client
        );
    }

    public function testAutowiredDouble(): void
    {
        static::assertInstanceOf(
            ManagerRegistry::class,
            $this->managerRegistry
        );

        static::assertInstanceOf(
            Stub::class,
            $this->managerRegistry
        );
    }

    public function testAutowireDoubleProperty(): void
    {
        static::assertSame(
            new \ReflectionProperty($this->userSetCommentNullMigration, 'managerRegistry')
                ->getValue($this->userSetCommentNullMigration),
            $this->managerRegistry
        );
    }

    public function testAutowiredTransportTester(): void
    {
        static::assertSame(
            static::getContainer()->get('messenger.transport.sync.draw.tester'),
            $this->transportTester
        );
    }

    public function testAutowiredParameter(): void
    {
        static::assertSame(
            'test_resolved',
            $this->parameter
        );
    }
}
