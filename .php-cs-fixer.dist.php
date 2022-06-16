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
        'phpdoc_order' => true,
        'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
        'logical_operators' => true,
        'no_useless_return' => true,
        'global_namespace_import' => ['import_classes' => false, 'import_constants' => false, 'import_functions' => false],
        'list_syntax' => ['syntax' => 'short'],
        'multiline_whitespace_before_semicolons' => ['strategy' => 'no_multi_line'],
        'no_extra_blank_lines' => true,
        'no_superfluous_phpdoc_tags' => ['allow_unused_params' => true, 'allow_mixed' => true, 'remove_inheritdoc' => true],
        'no_useless_else' => true,
    ])
    ->setFinder($finder)
;
