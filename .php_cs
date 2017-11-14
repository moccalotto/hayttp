<?php

$rules = [
    '@Symfony' => true,
    'concat_space' => ['spacing' => 'one'],
    'return_type_declaration' => ['space_before' => 'one'],
    'yoda_style' => false,
];

return PhpCsFixer\Config::create()
    ->setRules($rules)
    ->setFinder(PhpCsFixer\Finder::create()->in(__DIR__ . '/src'));
