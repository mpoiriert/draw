<?php

class StaticPrivateMethod
{
    public function execute(): void
    {
        static::foo();
    }

    private static function foo(): void
    {
    }
}
