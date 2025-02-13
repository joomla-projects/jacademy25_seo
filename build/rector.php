<?php

/**
 * Rector config file.
 *
 * @package            Joomla.Build
 * @copyright          (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license            GNU General Public License version 2 or later; see LICENSE.txt
 */

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Php81\Rector\Property\ReadOnlyPropertyRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\ValueObject\PhpVersion;

$rectorConfig = RectorConfig::configure();

// Setup parallel
$rectorConfig->withParallel(120, 2, 10);

// Min version
$rectorConfig->withPhpVersion(PhpVersion::PHP_81);

// Complete sets with rules
$rectorConfig->withSets([
    LevelSetList::UP_TO_PHP_81,
]);

// Skip some rules and folders/files
$rectorConfig->withSkip([
    // Ignore read only constructor property changes
    ReadOnlyPropertyRector::class,
    // Ignore template and layout files
    '*/tmpl/*',
    '*/layouts/*',
    // Ignore vendor
    '*/vendor/*',
]);

// The bootstrap file, which finds the core classes and loads the extension namespace
$rectorConfig->withBootstrapFiles([__DIR__ . '/phpstan/joomla-bootstrap.php']);

return $rectorConfig;
