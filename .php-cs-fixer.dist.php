<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

$header = <<<'HEADER'
This file is part of php-llm/llm-chain.

(c) Christopher Hertel <mail@christopher-hertel.de>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
HEADER;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'header_comment' => [
            'header' => $header,
            'location' => 'after_declare_strict',
        ],
    ])
    ->setFinder($finder)
    ;
