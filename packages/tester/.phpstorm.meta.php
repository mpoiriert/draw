<?php namespace PHPSTORM_META {
    $STATIC_METHOD_TYPES = [
        \Draw\Component\Tester\ServiceTesterTrait::getService('') => [
            "" == "@",
        ],
    ];

    override(
        \Draw\Component\Tester\MockBuilderTrait::createMockWithExtraMethods(0),
        map([""=>"$0"])
    );
}