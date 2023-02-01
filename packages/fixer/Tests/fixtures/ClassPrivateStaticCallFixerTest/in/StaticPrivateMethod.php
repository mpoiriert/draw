<?php

class StaticPrivateMethod
{
    public function execute()
    {
        static::foo();
    }

    private static function foo()
    {
    }
}