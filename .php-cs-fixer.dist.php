<?php

$finder = (new \PhpCsFixer\Finder)
    ->in(__DIR__)
    ->exclude('var')
;

return (new \PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        '@Symfony:risky' => true,
        'array_syntax' => ['syntax' => 'short'],
        'php_unit_test_case_static_method_calls' => true,
        'phpdoc_order' => true
    ])
    ->setFinder($finder)
;
