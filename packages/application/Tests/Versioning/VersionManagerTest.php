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
            static::createStub(ConfigurationRegistryInterface::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(static::isInstanceOf(FetchRunningVersionEvent::class))
            ->willReturnArgument(0)
        ;

        static::assertNull($service->getRunningVersion());

        // Multiple call will not trigger multiple event
        static::assertNull($service->getRunningVersion());
    }

    public function testGetRunningVersion(): void
    {
        $service = new VersionManager(
            static::createStub(ConfigurationRegistryInterface::class),
            $eventDispatcher = $this->createMock(EventDispatcherInterface::class)
        );

        $version = uniqid('version-');

        $eventDispatcher
            ->expects(static::once())
            ->method('dispatch')
            ->with(
                static::callback(static function (FetchRunningVersionEvent $event) use ($version) {
                    $event->setRunningVersion($version);

                    return true;
                })
            )
            ->willReturnArgument(0)
        ;

        static::assertSame(
            $version,
            $service->getRunningVersion()
        );
    }

    public function testUpdateDeployedVersion(): void
    {
        $service = new VersionManager(
            $configurationRegistry = $this->createMock(ConfigurationRegistryInterface::class),
            static::createStub(EventDispatcherInterface::class)
        );

        $version = uniqid('version-');

        ReflectionAccessor::setPropertyValue(
            $service,
            'runningVersion',
            $version
        );

        $configurationRegistry
            ->expects(static::once())
            ->method('set')
            ->with($service::CONFIG, $version)
        ;

        $service->updateDeployedVersion();
    }

    public function testGetDeployedVersion(): void
    {
        $service = new VersionManager(
            $configurationRegistry = $this->createMock(ConfigurationRegistryInterface::class),
            static::createStub(EventDispatcherInterface::class)
        );

        $configurationRegistry
            ->expects(static::once())
            ->method('get')
            ->with($service::CONFIG)
            ->willReturn($version = uniqid('version-'))
        ;

        static::assertSame(
            $version,
            $service->getDeployedVersion()
        );
    }

    public function testIsUpToDate(): void
    {
        $service = new VersionManager(
            $configurationRegistry = $this->createMock(ConfigurationRegistryInterface::class),
            static::createStub(EventDispatcherInterface::class)
        );

        $configurationRegistry
            ->expects(static::once())
            ->method('get')
            ->with($service::CONFIG)
            ->willReturn($version = uniqid('version-'))
        ;

        ReflectionAccessor::setPropertyValue(
            $service,
            'runningVersion',
            $version
        );

        static::assertTrue($service->isUpToDate());
    }

    public function testIsUpToDateFalse(): void
    {
        $service = new VersionManager(
            $configurationRegistry = $this->createMock(ConfigurationRegistryInterface::class),
            static::createStub(EventDispatcherInterface::class)
        );

        $configurationRegistry
            ->expects(static::once())
            ->method('get')
            ->with($service::CONFIG)
            ->willReturn(uniqid('version-'))
        ;

        ReflectionAccessor::setPropertyValue(
            $service,
            'runningVersion',
            uniqid('version-')
        );

        static::assertFalse($service->isUpToDate());
    }
}
