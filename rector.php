<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/QC_production',
        __DIR__ . '/QC_web',
    ])
    ->withSkip([
        __DIR__ . '/vendor',
    ])
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
    ]);
