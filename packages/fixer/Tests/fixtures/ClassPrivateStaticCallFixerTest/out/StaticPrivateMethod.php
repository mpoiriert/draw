<?php

class StaticPrivateMethod
{
    public function execute()
    {
        self::foo();
    }

    private static function foo()
    {
    }
}