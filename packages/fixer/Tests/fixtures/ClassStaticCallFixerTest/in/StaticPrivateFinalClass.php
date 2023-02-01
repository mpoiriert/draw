<?php

final class StaticPrivateFinalClass
{
    private static string $toto = '';

    private const TOTO = '';

    public function execute(): void
    {
        echo static::TOTO;
        echo static::$toto;
        static::foo();
    }

    private static function foo(): void
    {
    }
}
