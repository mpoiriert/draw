<?php

namespace Draw\Component\Application\Tests\Versioning;

use Draw\Component\Application\Versioning\Event\FetchRunningVersionEvent;
use Draw\Component\Application\Versioning\VersionManager;
use Draw\Component\Core\Reflection\ReflectionAccessor;
use Draw\Contracts\Application\ConfigurationRegistryInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * @internal
 */
#[CoversClass(VersionManager::class)]
class VersionManagerTest extends TestCase
{
    public function testGetRunningVersionNotFound(): void
    {
        $service = new VersionManager(
            $this->createStub(ConfigurationRegistryInterface::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(FetchRunningVersionEvent::class))
            ->willReturnArgument(0)
        ;

        $this->assertNull($service->getRunningVersion());

        // Multiple call will not trigger multiple event
        $this->assertNull($service->getRunningVersion());
    }

    public function testGetRunningVersion(): void
    {
        $service = new VersionManager(
            $this->createStub(ConfigurationRegistryInterface::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        $version = uniqid('version-');

        $eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(
                $this->callback(static function (FetchRunningVersionEvent $event) use ($version) {
                    $event->setRunningVersion($version);

                    return true;
                })
            )
            ->willReturnArgument(0)
        ;

        $this->assertSame(
            $version,
            $service->getRunningVersion()
        );
    }

    public function testUpdateDeployedVersion(): void
    {
        $service = new VersionManager(
            $configurationRegistry = $this->createMock(ConfigurationRegistryInterface::class),
            $this->createStub(EventDispatcherInterface::class)
        );

        $version = uniqid('version-');

        ReflectionAccessor::setPropertyValue(
            $service,
            'runningVersion',
            $version
        );

        $configurationRegistry
            ->expects($this->once())
            ->method('set')
            ->with($service::CONFIG, $version)
        ;

        $service->updateDeployedVersion();
    }

    public function testGetDeployedVersion(): void
    {
        $service = new VersionManager(
            $configurationRegistry = $this->createMock(ConfigurationRegistryInterface::class),
            $this->createStub(EventDispatcherInterface::class)
        );

        $configurationRegistry
            ->expects($this->once())
            ->method('get')
            ->with($service::CONFIG)
            ->willReturn($version = uniqid('version-'))
        ;

        $this->assertSame(
            $version,
            $service->getDeployedVersion()
        );
    }

    public function testIsUpToDate(): void
    {
        $service = new VersionManager(
            $configurationRegistry = $this->createMock(ConfigurationRegistryInterface::class),
            $this->createStub(EventDispatcherInterface::class)
        );

        $configurationRegistry
            ->expects($this->once())
            ->method('get')
            ->with($service::CONFIG)
            ->willReturn($version = uniqid('version-'))
        ;

        ReflectionAccessor::setPropertyValue(
            $service,
            'runningVersion',
            $version
        );

        $this->assertTrue($service->isUpToDate());
    }

    public function testIsUpToDateFalse(): void
    {
        $service = new VersionManager(
            $configurationRegistry = $this->createMock(ConfigurationRegistryInterface::class),
            $this->createStub(EventDispatcherInterface::class)
        );

        $configurationRegistry
            ->expects($this->once())
            ->method('get')
            ->with($service::CONFIG)
            ->willReturn(uniqid('version-'))
        ;

        ReflectionAccessor::setPropertyValue(
            $service,
            'runningVersion',
            uniqid('version-')
        );

        $this->assertFalse($service->isUpToDate());
    }
}
