<?php

$finder = (new \PhpCsFixer\Finder)
    ->in(__DIR__)
    ->exclude('var')
;

return (new \PhpCsFixer\Config())
    ->setRules([
        '@PHP74Migration' => true,
        '@PHP74Migration:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit57Migration:risky' => true,
        '@PHPUnit60Migration:risky' => true,
        '@PHPUnit75Migration:risky' => true,
        '@PHPUnit84Migration:risky' => true,
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
        'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => true],
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => true],
        'single_line_throw' => false,
        '@DoctrineAnnotation' => true,
        // Disabled risky for now
        'declare_strict_types' => false,
        'void_return' => false,
    ])
    ->setFinder($finder)
;
