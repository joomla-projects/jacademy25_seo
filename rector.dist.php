<?php
/**
 * @package    Joomla.Site
 *
 * @copyright  (C) 2025 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

use Rector\Config\RectorConfig;
use Rector\Php80\Rector\Identical\StrStartsWithRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/administrator/components',
        __DIR__ . '/components',
    ])
    ->withFileExtensions(['php'])
    ->withSkip([
        '*/tmpl/*',
        '*/layouts*/',
    ])
    ->withRules([
        StrStartsWithRector::class,
    ]);
