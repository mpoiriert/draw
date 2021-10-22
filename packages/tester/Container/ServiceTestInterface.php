<?php

namespace Draw\Component\Tester\Container;

interface ServiceTestInterface
{
    public static function getServiceIdsToTest(): iterable;
}
