<?php

$rules = [
    '@Symfony' => true,
    'concat_space' => ['spacing' => 'one'],
    'return_type_declaration' => ['space_before' => 'one'],
    'yoda_style' => false,
    'binary_operator_spaces' => [
        'align_double_arrow' => null,
        'align_equals' => null,
    ],
];

return PhpCsFixer\Config::create()
    ->setRules($rules)
    ->setFinder(PhpCsFixer\Finder::create()->in(__DIR__ . '/src'));
