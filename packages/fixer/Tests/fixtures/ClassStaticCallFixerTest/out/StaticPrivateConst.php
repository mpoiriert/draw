<?php

class StaticPrivateConst
{
    private const FOO = 'foo';

    public function execute(): void
    {
        echo self::FOO;
    }
}
