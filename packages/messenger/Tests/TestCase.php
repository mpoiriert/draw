<?php

namespace Draw\Component\Messenger\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ConnectionRegistry;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase implements ConnectionRegistry
{
    private static ?Connection $connection = null;

    public function getDefaultConnectionName(): string
    {
        return 'default';
    }

    public static function loadDefaultConnection(): Connection
    {
        if (null === self::$connection) {
            self::$connection = DriverManager::getConnection(['url' => getenv('DATABASE_URL')]);
        }

        return self::$connection;
    }

    public function getConnection($name = null): Connection
    {
        $name = $name ?: $this->getDefaultConnectionName();
        if ('default' !== $name) {
            throw new InvalidArgumentException('Connection named ['.$name.'] does not exists.');
        }

        return static::loadDefaultConnection();
    }

    public function getConnections(): array
    {
        return [
            'default' => $this->getConnection(),
        ];
    }

    public function getConnectionNames(): array
    {
        return ['default'];
    }
}
