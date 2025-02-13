<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/administrator/components',
        //__DIR__ . '/administrator/modules',
        //__DIR__ . '/administrator/templates',
        __DIR__ . '/components',
        //__DIR__ . '/modules',
        //__DIR__ . '/libraries/src/MVC',
        //__DIR__ . '/libraries/src',
    ])
    ->withFileExtensions(['php'])
    ->withSkip(
        [
            '*/tmpl/*',
            '*/layouts/*',
            //'*/View/*',
           //__DIR__.'/libraries/src/MVC'
        ]
    )
    ->withRules([\Rector\CodeQuality\Rector\If_\CombineIfRector::class]);