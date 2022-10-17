<?php

namespace PHPSTORM_META;

override(
    \Draw\Component\Tester\MockTrait::createMockWithExtraMethods(),
    map(["" => "@"])
);

override(
    \Draw\Component\Tester\MockTrait::mockProperty(2),
    map([""=>"$2"])
);