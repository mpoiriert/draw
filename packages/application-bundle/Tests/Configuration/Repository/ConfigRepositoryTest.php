<?php

namespace Draw\Bundle\ApplicationBundle\Tests\Configuration\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Draw\Bundle\ApplicationBundle\Configuration\Entity\Config;
use Draw\Bundle\ApplicationBundle\Configuration\Repository\ConfigRepository;
use Draw\Bundle\ApplicationBundle\Tests\TestCase;
use Draw\Contracts\Application\ConfigurationRegistryInterface;

/**
 * @covers \Draw\Bundle\ApplicationBundle\Configuration\Repository\ConfigRepository
 */
class ConfigRepositoryTest extends TestCase
{
    /**
     * @var ConfigRepository
     */
    private $service;

    /**
     * @beforeClass
     * @afterClass
     */
    public static function cleanUp(): void
    {
        static::getService(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->delete(Config::class, 'config')
            ->andWhere('config.id = :name')
            ->getQuery()
            ->execute(['name' => 'value']);
    }

    public function setUp(): void
    {
        $this->service = static::getService(ConfigRepository::class);
    }

    public function testConstruct(): void
    {
        $this->assertInstanceOf(ServiceEntityRepository::class, $this->service);
        $this->assertInstanceOf(ConfigurationRegistryInterface::class, $this->service);
    }

    public function testHasNotSet(): void
    {
        $this->assertFalse($this->service->has('value'));
    }

    /**
     * @depends testHasNotSet
     */
    public function testGetDefault(): void
    {
        $this->assertNull($this->service->get('value'));

        $this->assertTrue($this->service->get('value', true));
    }

    /**
     * @doesNotPerformAssertions
     */
    public function testSet(): void
    {
        $this->service->set('value', 'the-value');
    }

    /**
     * @depends testSet
     */
    public function testHasSet(): void
    {
        $this->assertTrue($this->service->has('value'));
    }

    /**
     * @depends testHasSet
     */
    public function testGetSet(): void
    {
        $this->assertSame('the-value', $this->service->get('value'));
    }

    /**
     * @depends testHasSet
     *
     * @doesNotPerformAssertions
     */
    public function testDelete(): void
    {
        $this->service->delete('value');
    }

    /**
     * @depends testDelete
     */
    public function testHasAfterDelete(): void
    {
        $this->assertFalse($this->service->has('value'));
    }

    /**
     * @depends testGetSet
     */
    public function testGetValueChangeFromOtherScope(): void
    {
        $this->service->set('value', 'the-value');
        $this->assertSame('the-value', $this->service->get('value'));

        static::getService(EntityManagerInterface::class)
            ->createQueryBuilder()
            ->update(Config::class, 'config')
            ->set('config.data', ':data')
            ->andWhere('config.id = :name')
            ->getQuery()
            ->execute(
                ['name' => 'value', 'data' => json_encode(['value' => 'new-value'])]
            );

        $this->assertSame('new-value', $this->service->get('value'));
    }

    public function provideTestSetGetKeepType(): iterable
    {
        yield 'string' => [
            'value',
        ];

        yield 'boolean' => [
            true,
        ];

        yield 'array' => [
            ['key' => 'value'],
        ];

        yield 'integer' => [
            1,
        ];

        yield 'float' => [
            1.5,
        ];
    }

    /**
     * @dataProvider provideTestSetGetKeepType
     */
    public function testSetGetKeepType($value): void
    {
        $this->service->set('value', $value);
        $this->assertSame($value, $this->service->get('value'));
    }
}
