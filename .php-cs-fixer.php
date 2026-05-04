<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php')
    ->notPath('vendor')
    ->notPath('storage');

return (new Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                             => true,
        '@PHP82Migration'                    => true,
        'array_syntax'                       => ['syntax' => 'short'],
        'ordered_imports'                    => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'                  => true,
        'not_operator_with_successor_space'  => true,
        'trailing_comma_in_multiline'        => true,
        'phpdoc_scalar'                      => true,
        'unary_operator_spaces'              => true,
        'binary_operator_spaces'             => true,
        'blank_line_before_statement'        => [
            'statements' => ['break', 'continue', 'declare', 'return', 'throw', 'try'],
        ],
        'phpdoc_single_line_var_spacing'     => true,
        'phpdoc_var_without_name'            => true,
        'class_attributes_separation'        => [
            'elements' => ['method' => 'one', 'property' => 'one'],
        ],
        'method_argument_space'              => [
            'on_multiline' => 'ensure_fully_multiline',
        ],
        'single_trait_insert_per_statement'  => true,
        'declare_strict_types'               => true,
    ])
    ->setFinder($finder);
