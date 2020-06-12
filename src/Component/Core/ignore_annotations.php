<?php

$namespaces = [
    Draw\Bundle\DashboardBundle\DrawDashboardBundle::class => [
        "Draw\Bundle\DashboardBundle\Annotations",
    ],
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
