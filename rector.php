<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector;
use Rector\PHPUnit\Set\PHPUnitSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/examples',
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withPhpSets(php82: true)
    ->withSets([
        PHPUnitSetList::PHPUNIT_110,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
    ])
    ->withImportNames(importNames: true, importShortClasses: false)
    ->withSkip([
        ClosureToArrowFunctionRector::class,
    ])
    ->withTypeCoverageLevel(0);
