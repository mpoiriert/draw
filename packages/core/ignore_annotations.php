<?php

if (class_exists(\Doctrine\Common\Annotations\AnnotationRegistry::class)) {
    \Doctrine\Common\Annotations\AnnotationRegistry::registerUniqueLoader('class_exists');
}

$namespaces = [
    Draw\Component\OpenApi\OpenApi::class => [
        "Draw\Component\Configuration",
        "Draw\Component\OpenApi\Schema",
    ],
];

foreach ($namespaces as $classToCheck => $namespacesToIgnore) {
    Draw\Component\Core\Annotation\Tool::ignoreNamespacesBaseOnClassExistence($classToCheck, $namespacesToIgnore);
}
