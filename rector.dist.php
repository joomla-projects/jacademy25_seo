<?php
/**
 * @package        Joomla.Site
 *
 * @copyright  (C) 2021 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * This is the configuration file for rector
 *
 * @link https://github.com/rectorphp/rector
 * @link https://getrector.com/
 *
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
