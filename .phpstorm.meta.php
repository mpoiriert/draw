<?php namespace PHPSTORM_META {
    $STATIC_METHOD_TYPES = [
        \Prophecy\Prophecy\ObjectProphecy::reveal('') => [
            "" == "@",
        ],
        \Draw\Component\Tester\ServiceTesterTrait::getService('') => [
            "" == "@",
        ],
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