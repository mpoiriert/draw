<?php namespace Draw\Component\Tester\DependencyInjection;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;

abstract class ExtensionTestCase extends TestCase
{
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

    /**
     * @dataProvider provideTestHasServiceDefinition
     *
     * @param string $id
     * @param string $aliasOf If the id is a alias it's a alias of which service ?
     */
    public function testHasServiceDefinition(string $id, string $aliasOf = null)
    {
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