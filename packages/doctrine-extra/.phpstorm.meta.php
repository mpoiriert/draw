<?php

namespace PHPSTORM_META;

override(
    \Draw\DoctrineExtra\Common\DataFixtures\ObjectReferenceTrait::getObjectReference(),
    map(["" => "@"])
);

override(
    \Draw\DoctrineExtra\ORM\EntityHandler::find(),
    map(["" => "@"])
);

override(
    \Draw\DoctrineExtra\ORM\EntityHandler::findAll(),
    map(["" => "@[]"])
);

override(
    \Draw\DoctrineExtra\ORM\EntityHandler::findBy(),
    map(["" => "@[]"])
);

override(
    \Draw\DoctrineExtra\ORM\EntityHandler::findOneBy(),
    map(["" => "@"])
);
