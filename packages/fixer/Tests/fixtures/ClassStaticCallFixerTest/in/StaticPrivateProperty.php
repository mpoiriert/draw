<?php

class StaticPrivateProperty
{
    private static string $foo = 'foo';

    public function execute(): void
    {
        echo self::$foo;
    }
}
