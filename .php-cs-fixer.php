<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Symfony\Component\Finder\Finder::create()
    ->notPath('vendor')
    ->notPath('bootstrap')
    ->notPath('storage')
    ->in(__DIR__)
    ->name('*.php')
    ->notName('*.blade.php');

$config = new Config();
return $config->setFinder($finder)
    ->setRules([
        '@PSR2' => true,
        'indentation_type' => true,
        'cast_spaces' => true,
        'concat_space' => [
            'spacing' => 'one'
        ],
        'function_typehint_space' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'array_syntax' => ['syntax' => 'short'],
        'ordered_imports' => ['sort_algorithm' => 'alpha'],
        'no_unused_imports' => true,
        'lowercase_cast' => true,
        'no_whitespace_before_comma_in_array' => true,
        'no_spaces_inside_parenthesis' => true,
        'no_spaces_around_offset' => true,
        'no_whitespace_in_blank_line' => true,
        'blank_line_after_namespace' => true,
        'phpdoc_indent' => true,
        'trim_array_spaces' => true,
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => [
            'statements' => ['return']
        ],
        'no_blank_lines_after_class_opening' => true,
        'no_blank_lines_after_phpdoc' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'extra',
                'throw',
                'use',
                'use_trait',
            ]
        ],
    ])
    ->setUsingCache(true);
