<?php namespace Draw\Component\Tester\DependencyInjection;

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

    abstract public function createExtension(): Extension;

    abstract public function provideTestHasServiceDefinition(): iterable;

    public function setUp(): void
    {
        $this->extension = $this->createExtension();
    }

    public static function setUpBeforeClass(): void
    {
        self::$definitions = [];
        self::$aliases = [];
    }

    /**
     * @dataProvider provideTestHasServiceDefinition
     *
     * @param string $id
     * @param string $aliasOf If the id is a alias it's a alias of which service ?
     */
    public function testHasServiceDefinition(string $id, string $aliasOf = null)
    {
        if ($aliasOf) {
            self::$aliases[] = $id;
        } else {
            self::$definitions[] = $id;
        }

        $containerBuilder = $this->load([]);
        $this->assertTrue(
            $containerBuilder->{$aliasOf ? 'hasAlias' : 'hasDefinition'}($id),
            sprintf(
                'Service id [%s] is not found',
                $id
            )
        );

        if ($aliasOf) {
            $this->assertEquals($aliasOf, $containerBuilder->getAlias($id));
        }
    }

    /**
     * @depends testHasServiceDefinition
     */
    public function testDefinitionsMatchChecks()
    {
        $expectedIds = array_values(
            array_diff(
                array_keys($this->load([])->getDefinitions()),
                array_keys((new ContainerBuilder())->getDefinitions())
            )
        );
        asort($expectedIds);

        $currentIds = self::$definitions;
        asort($currentIds);

        $this->assertSame(
            array_values($expectedIds),
            array_values($currentIds)
        );
    }

    /**
     * @depends testHasServiceDefinition
     */
    public function testAliasesMatchChecks()
    {
        $expectedIds = array_values(
            array_diff(
                array_keys($this->load([])->getAliases()),
                array_keys((new ContainerBuilder())->getAliases())
            )
        );
        asort($expectedIds);

        $currentIds = self::$aliases;
        asort($currentIds);

        $this->assertSame(
            array_values($expectedIds),
            array_values($currentIds)
        );
    }

    /**
     * @param array $config
     * @return ContainerBuilder
     */
    protected function load(array $config): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $this->extension->load($config, $containerBuilder);
        return $containerBuilder;
    }
}