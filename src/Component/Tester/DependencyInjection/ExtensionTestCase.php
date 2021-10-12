<?php

namespace Draw\Component\Tester\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

abstract class ExtensionTestCase extends TestCase
{
    protected static $definitions = [];
    protected static $aliases = [];

    /**
     * @var Extension
     */
    private $extension;

    /**
     * @var ContainerBuilder
     */
    private static $containerBuilder;

    abstract public function createExtension(): Extension;

    /**
     * Return the configuration that will be tested by this extension.
     */
    abstract public function getConfiguration(): array;

    abstract public function provideTestHasServiceDefinition(): iterable;

    protected function removeProvidedService(array $idsToRemove, iterable $providedServices): iterable
    {
        foreach ($providedServices as $providedService) {
            if (!in_array($providedService[0], $idsToRemove)) {
                yield $providedService;
            }
        }
    }

    public function setUp(): void
    {
        $this->extension = $this->createExtension();
        if (null === self::$containerBuilder) {
            self::$containerBuilder = $this->load($this->getConfiguration());
        }
    }

    public static function setUpBeforeClass(): void
    {
        self::$containerBuilder = null;
        self::$definitions = [];
        self::$aliases = [];
    }

    /**
     * @dataProvider provideTestHasServiceDefinition
     *
     * @param string $aliasOf If the id is a alias it's a alias of which service ?
     */
    public function testHasServiceDefinition(string $id, string $aliasOf = null)
    {
        if ($aliasOf) {
            self::$aliases[] = $id;
        } else {
            self::$definitions[] = $id;
        }

        $this->assertTrue(
            self::$containerBuilder->{$aliasOf ? 'hasAlias' : 'hasDefinition'}($id),
            sprintf(
                'Service id [%s] is not found',
                $id
            )
        );

        if ($aliasOf) {
            $this->assertEquals($aliasOf, self::$containerBuilder->getAlias($id));
        }
    }

    public function testDefinitionsMatchChecks()
    {
        $expectedIds = array_values(
            array_diff(
                array_keys(self::$containerBuilder->getDefinitions()),
                array_keys((new ContainerBuilder())->getDefinitions())
            )
        );
        asort($expectedIds);

        $currentIds = self::$definitions;
        asort($currentIds);

        $this->assertSame(
            array_values($expectedIds),
            array_values($currentIds),
            'Services available do not match.'
        );
    }

    public function testAliasesMatchChecks()
    {
        $expectedIds = array_values(
            array_diff(
                array_keys(self::$containerBuilder->getAliases()),
                array_keys((new ContainerBuilder())->getAliases())
            )
        );
        asort($expectedIds);

        $currentIds = self::$aliases;
        asort($currentIds);

        $this->assertSame(
            array_values($expectedIds),
            array_values($currentIds),
            'Alias available do not match.'
        );
    }

    /**
     * @param array $config The configuration will be pass as Extension::load([$config])
     */
    protected function load(array $config): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $this->preLoad($config, $containerBuilder);
        $this->extension->load([$config], $containerBuilder);

        return $containerBuilder;
    }

    /**
     * Override if you want to configure the container builder in some specific case.
     */
    protected function preLoad(array $config, ContainerBuilder $containerBuilder): void
    {
    }
}
