<?php

if (class_exists(\Doctrine\Common\Annotations\AnnotationRegistry::class)) {
    \Doctrine\Common\Annotations\AnnotationRegistry::registerUniqueLoader('class_exists');
}

$namespaces = [
    Draw\Component\OpenApi\OpenApi::class => [
        "Draw\Component\OpenApi\Schema",
    ],
    Draw\Bundle\OpenApiBundle\DrawOpenApiBundle::class => [
        "Draw\Bundle\OpenApiBundle\Request",
        "Draw\Bundle\OpenApiBundle\Response",
    ],
];

foreach ($namespaces as $classToCheck => $namespacesToIgnore) {
    Draw\Component\Core\Annotation\Tool::ignoreNamespacesBaseOnClassExistence($classToCheck, $namespacesToIgnore);
}
