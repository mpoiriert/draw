<?php namespace PHPSTORM_META {
    $STATIC_METHOD_TYPES = [
        \Prophecy\Prophecy\ObjectProphecy::reveal('') => [
            "" == "@",
        ],
        \Draw\Component\Tester\ServiceTesterTrait::getService('') => [
            "" == "@",
        ],
        \Symfony\Contracts\EventDispatcher\EventDispatcherInterface::dispatch('') => [
            "" == "@"
        ]
    ];
}

namespace Prophecy\Prophecy {
    class ObjectProphecy
    {
        public function reveal(string $class = null)
        {

        }
    }
}