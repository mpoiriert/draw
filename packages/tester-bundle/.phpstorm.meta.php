<?php

namespace PHPSTORM_META;

override(
    \Draw\Bundle\TesterBundle\DependencyInjection\ServiceTesterTrait::getService(),
    map(["" => "@"])
);
