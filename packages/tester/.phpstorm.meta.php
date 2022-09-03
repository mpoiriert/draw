<?php

namespace PHPSTORM_META;

override(
    \Draw\Component\Tester\MockTrait::createMockWithExtraMethods(),
    map(["" => "@"])
);

override(
    \App\Component\TestHelper\MockerTrait::mockProperty(2),
    map([""=>"$2"])
);