<?php

namespace Draw\Component\Messenger\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Persistence\ConnectionRegistry;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase implements ConnectionRegistry
{
    private static $connection = null;

    public function getDefaultConnectionName()
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

    public function getConnection($name = null)
    {
        $name = $name ?: $this->getDefaultConnectionName();
        if ('default' !== $name) {
            throw new \InvalidArgumentException('Connection named ['.$name.'] does not exists.');
        }

        return static::loadDefaultConnection();
    }

    public function getConnections()
    {
        return [
            'default' => $this->getConnection(),
        ];
    }

    public function getConnectionNames()
    {
        return ['default'];
    }
}
