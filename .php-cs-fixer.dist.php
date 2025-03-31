<?php

$finder = (new \PhpCsFixer\Finder)
    ->in(__DIR__)
    ->exclude('var')
    ->exclude('bin')
    ->exclude('packages/fixer/Tests/fixtures/ClassPrivateStaticCallFixerTest')
    ->exclude('packages/fixer/Tests/fixtures/ClassStaticCallFixerTest')
;

$config = (new \PhpCsFixer\Config())
    ->setCacheFile(__DIR__ . '/var/.php-cs-fixer.cache')
    ->setRules([
        '@PHP80Migration:risky' => true,
        '@PHP82Migration' => true,
        '@PhpCsFixer' => true,
        '@PhpCsFixer:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHPUnit57Migration:risky' => true,
        '@PHPUnit60Migration:risky' => true,
        '@PHPUnit75Migration:risky' => true,
        '@PHPUnit84Migration:risky' => true,
        '@PHPUnit91Migration:risky' => true,
        '@PHPUnit100Migration:risky' => true,
        'header_comment' => ['header' => '',], // Make sure we remove any header comments
        'phpdoc_add_missing_param_annotation' => ['only_untyped' => true], // @PhpCsFixer use default 'true', we don't want php doc that are already typed
        'method_chaining_indentation' => false, // @PhpCsFixer use default 'true' impact readability on symfony configuration and sonata admin
        'phpdoc_no_empty_return' => false, // @PhpCsFixer use default 'true' draw/open-api require empty phpdoc for documentation
        'final_internal_class' => false, // All test case are made internal, but we do not want them to be final
        // Disabled for now
        'strict_comparison' => false, // @PhpCsFixer:risky change this, we do need to loosely compare 2 object at some places, might find a way to fix this.
        'php_unit_test_class_requires_covers' => false, // @PhpCsFixer use default 'true' putting covers nothing by default
        'declare_strict_types' => false,
    ])
    ->setFinder($finder);
;

return \Draw\Fixer\RuleSet::adjust($config);
