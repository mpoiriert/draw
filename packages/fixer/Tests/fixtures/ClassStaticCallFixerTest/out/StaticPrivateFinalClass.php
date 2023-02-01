<?php

final class StaticPrivateFinalClass
{
    private static string $toto = '';

    private const TOTO = '';

    public function execute(): void
    {
        echo self::TOTO;
        echo self::$toto;
        self::foo();
    }

    private static function foo(): void
    {
    }
}
