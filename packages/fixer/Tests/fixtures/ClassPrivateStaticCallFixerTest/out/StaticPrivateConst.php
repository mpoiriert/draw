<?php

class StaticPrivateConst
{
    private const FOO = 'foo';

    public function execute()
    {
        echo self::FOO;
    }
}