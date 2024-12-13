<?php
/**
 * @package   DPDocker
 * @copyright Copyright (C) 2024 Digital Peak GmbH. <https://www.digital-peak.com>
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 */

declare(strict_types=1);

use Rector\CodeQuality\Rector\Class_\CompleteDynamicPropertiesRector;
use Rector\CodeQuality\Rector\LogicalAnd\LogicalToBooleanRector;
use Rector\CodingStyle\Rector\Stmt\NewlineAfterStatementRector;
use Rector\CodingStyle\Rector\Use_\SeparateMultiUseImportsRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPrivateMethodRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessParamTagRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUselessReturnTagRector;
use Rector\DeadCode\Rector\If_\RemoveAlwaysTrueIfConditionRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\DeadCode\Rector\StaticCall\RemoveParentCallWithoutParentRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\Privatization\Rector\ClassMethod\PrivatizeFinalClassMethodRector;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector;
use Rector\ValueObject\PhpVersion;

$rectorConfig = RectorConfig::configure();

// Min version
$rectorConfig->withPhpVersion(PhpVersion::PHP_81);

// No short class import
$rectorConfig->withImportNames(importShortClasses: false, removeUnusedImports: true);

// Tabs for indentation
$rectorConfig->withIndent(' ', 4);

// Complete sets with rules
$rectorConfig->withSets([
    LevelSetList::UP_TO_PHP_81,
    SetList::CODE_QUALITY,
    SetList::CODING_STYLE,
    SetList::DEAD_CODE,
    SetList::STRICT_BOOLEANS,
    //SetList::NAMING,
    SetList::PRIVATIZATION,
    //SetList::TYPE_DECLARATION,
    SetList::EARLY_RETURN,
    SetList::INSTANCEOF,
]);

// Skip some rules and folders/files
$rectorConfig->withSkip([
    // Keep Joomla version compare
    UnwrapFutureCompatibleIfPhpVersionRector::class,
    // Keep docs
    RemoveUselessParamTagRector::class,
    RemoveUselessReturnTagRector::class,
    // Keep <?php } ? on same line
    NewlineAfterStatementRector::class,
    // Do not remove methods and properties
    RemoveUnusedPrivateMethodRector::class,
    // Stay safe and do not remove code
    RemoveParentCallWithoutParentRector::class,
    // Keep the or in JEXEC
    LogicalToBooleanRector::class,
    // No return never
    ReturnNeverTypeRector::class,
    // No splitting if with ||
    ChangeOrIfContinueToMultiContinueRector::class,
    // Multiuse should be allowed in component classes
    SeparateMultiUseImportsRector::class => ['*Component.php'],
    // JLoader check
    RemoveAlwaysTrueIfConditionRector::class => [__DIR__ . '/../libraries/bootstrap.php'],
    // Do not add $app and $db as properties
    CompleteDynamicPropertiesRector::class => [__DIR__ . '/../libraries/src/Plugin/CMSPlugin.php'],
    // The setArgument function in events uses unused functions
    PrivatizeFinalClassMethodRector::class => ['*/Event/*'],
    // Ignore vendor
    '*/vendor/*'
]);

// The bootstrap file, which finds the core classes and loads the extension namespace
$rectorConfig->withBootstrapFiles([__DIR__ . '/phpstan/joomla-bootstrap.php']);

return $rectorConfig;
