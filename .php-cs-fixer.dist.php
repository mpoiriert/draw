<?php

$finder = (new \PhpCsFixer\Finder)
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('bin')
    ->exclude('packages/fixer/Tests/fixtures/ClassPrivateStaticCallFixerTest')
    ->exclude('packages/fixer/Tests/fixtures/ClassStaticCallFixerTest')
;

$config = (new \PhpCsFixer\Config())
    ->setRules([
        '@PHP8x0Migration:risky' => true,
        '@PHP8x2Migration' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit5x7Migration:risky' => true,
        '@PHPUnit6x0Migration:risky' => true,
        '@PHPUnit7x5Migration:risky' => true,
        '@PHPUnit8x4Migration:risky' => true,
        '@PHPUnit9x1Migration:risky' => true,
        '@PHPUnit10x0Migration:risky' => true,
        'header_comment' => ['header' => '',], // Make sure we remove any header comments
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => true], // @PhpCsFixer use default 'true', we don't want php doc that are already typed
        'method_chaining_indentation' => false, // @PhpCsFixer use default 'true' impact readability on symfony configuration and sonata admin
        'phpdoc_no_empty_return' => false, // @PhpCsFixer use default 'true' draw/open-api require empty phpdoc for documentation
        'final_internal_class' => false, // All test case are made internal, but we do not want them to be final
        // Disabled for now
        'strict_comparison' => false, // @PhpCsFixer:risky change this, we do need to loosely compare 2 object at some places, might find a way to fix this.
        'php_unit_test_class_requires_covers' => false, // @PhpCsFixer use default 'true' putting covers nothing by default
        'declare_strict_types' => false,
        'attribute_block_no_spaces' => false,
    ])
    ->setFinder($finder);
;

return \Draw\Fixer\RuleSet::adjust($config);
