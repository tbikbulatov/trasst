<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'config',
        'var',
    ])
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'array_syntax' => ['syntax' => 'short'],
        'date_time_immutable' => true,
        'declare_strict_types' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true
        ],
        'no_leading_import_slash' => true,
        'no_unused_imports' => true,
        'nullable_type_declaration_for_default_null_value' => false,
        'ordered_imports' => [
            'imports_order' => ['class', 'function', 'const'],
            'sort_algorithm' => 'alpha',
        ],
        'trailing_comma_in_multiline' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
