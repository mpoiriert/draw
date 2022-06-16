<?php

$finder = (new \PhpCsFixer\Finder)
    ->in(__DIR__)
    ->exclude('var')
;

return (new \PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'php_unit_test_case_static_method_calls' => true,
    ])
    ->setFinder($finder)
;
