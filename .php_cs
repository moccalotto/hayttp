<?php

use Symfony\CS\Config\Config;
use Symfony\CS\FixerInterface;
use Symfony\CS\Finder\DefaultFinder;

$fixers = [
    '-psr0',
    'psr2',
    'psr1',
    'symfony',
    '+concat_with_spaces',
    'concat_with_spaces',
    'short_array_syntax',
    'align_equals',
];

return Config::create()
    ->finder(DefaultFinder::create()->in(__DIR__))
    ->fixers($fixers)
    ->level(FixerInterface::SYMFONY_LEVEL)
    ->setUsingCache(false);
