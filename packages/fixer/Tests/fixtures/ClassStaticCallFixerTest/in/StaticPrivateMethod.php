<?php

class StaticPrivateMethod
{
    public function execute(): void
    {
        self::foo();
    }

    private static function foo(): void
    {
    }
}
