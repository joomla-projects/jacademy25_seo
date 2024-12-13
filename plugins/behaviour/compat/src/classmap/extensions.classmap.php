<?php

use Joomla\Component\Actionlogs\Administrator\Plugin\ActionLogPlugin;
use Joomla\Component\Content\Site\Helper\RouteHelper;
use Joomla\Component\Fields\Administrator\Plugin\FieldsListPlugin;
use Joomla\Component\Fields\Administrator\Plugin\FieldsPlugin;
use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Parser;
use Joomla\Component\Finder\Administrator\Indexer\Query;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Component\Finder\Administrator\Indexer\Taxonomy;
use Joomla\Component\Finder\Administrator\Indexer\Token;
use Joomla\Component\Privacy\Administrator\Export\Domain;
use Joomla\Component\Privacy\Administrator\Export\Field;
use Joomla\Component\Privacy\Administrator\Export\Item;
use Joomla\Component\Privacy\Administrator\Plugin\PrivacyPlugin;
use Joomla\Component\Privacy\Administrator\Removal\Status;
use Joomla\Component\Privacy\Administrator\Table\RequestTable;
use Joomla\Component\Tags\Administrator\Table\TagTable;

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
\defined('_JEXEC') or die;

// Class map of the core extensions
JLoader::registerAlias('ActionLogPlugin', ActionLogPlugin::class, '5.0');

JLoader::registerAlias('FieldsPlugin', FieldsPlugin::class, '5.0');
JLoader::registerAlias('FieldsListPlugin', FieldsListPlugin::class, '5.0');

JLoader::registerAlias('PrivacyExportDomain', Domain::class, '5.0');
JLoader::registerAlias('PrivacyExportField', Field::class, '5.0');
JLoader::registerAlias('PrivacyExportItem', Item::class, '5.0');
JLoader::registerAlias('PrivacyPlugin', PrivacyPlugin::class, '5.0');
JLoader::registerAlias('PrivacyRemovalStatus', Status::class, '5.0');
JLoader::registerAlias('PrivacyTableRequest', RequestTable::class, '5.0');

JLoader::registerAlias('TagsTableTag', TagTable::class, '5.0');

JLoader::registerAlias('ContentHelperRoute', RouteHelper::class, '5.0');

JLoader::registerAlias('FinderIndexerAdapter', Adapter::class, '5.0');
JLoader::registerAlias('FinderIndexerHelper', Helper::class, '5.0');
JLoader::registerAlias('FinderIndexer', Indexer::class, '5.0');
JLoader::registerAlias('FinderIndexerParser', Parser::class, '5.0');
JLoader::registerAlias('FinderIndexerQuery', Query::class, '5.0');
JLoader::registerAlias('FinderIndexerResult', Result::class, '5.0');
JLoader::registerAlias('FinderIndexerTaxonomy', Taxonomy::class, '5.0');
JLoader::registerAlias('FinderIndexerToken', Token::class, '5.0');
