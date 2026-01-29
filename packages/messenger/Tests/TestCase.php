<?php

namespace Draw\Component\Messenger\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Tools\DsnParser;
use Doctrine\Persistence\ConnectionRegistry;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * @internal
 */
class TestCase extends BaseTestCase implements ConnectionRegistry
{
    private static ?Connection $connection = null;

    public function getDefaultConnectionName(): string
    {
        return 'default';
    }

    public static function loadDefaultConnection(): Connection
    {
        if (null !== self::$connection) {
            return self::$connection;
        }

        $url = getenv('DATABASE_URL');
        if (!$url) {
            throw new \RuntimeException('DATABASE_URL environment variable is not set');
        }

        $dsnParser = new DsnParser(['mysql' => 'pdo_mysql', 'postgresql' => 'pdo_pgsql']);
        self::$connection = DriverManager::getConnection($dsnParser->parse($url));

        return self::$connection;
    }

    public function getConnection($name = null): Connection
    {
        $name = $name ?: $this->getDefaultConnectionName();
        if ('default' !== $name) {
            throw new \InvalidArgumentException('Connection named ['.$name.'] does not exists.');
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
        return ['default' => 'default'];
    }
}
