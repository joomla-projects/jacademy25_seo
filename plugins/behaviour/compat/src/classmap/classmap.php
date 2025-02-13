<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2014 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
\defined('_JEXEC') or die;

require_once __DIR__ . '/extensions.classmap.php';

JLoader::registerAlias('JRegistry', \Joomla\Registry\Registry::class, '6.0');
JLoader::registerAlias('JRegistryFormatIni', \Joomla\Registry\Format\Ini::class, '6.0');
JLoader::registerAlias('JRegistryFormatJson', \Joomla\Registry\Format\Json::class, '6.0');
JLoader::registerAlias('JRegistryFormatPhp', \Joomla\Registry\Format\Php::class, '6.0');
JLoader::registerAlias('JRegistryFormatXml', \Joomla\Registry\Format\Xml::class, '6.0');
JLoader::registerAlias('JStringInflector', \Joomla\String\Inflector::class, '6.0');
JLoader::registerAlias('JStringNormalise', \Joomla\String\Normalise::class, '6.0');
JLoader::registerAlias('JData', \Joomla\Data\DataObject::class, '6.0');
JLoader::registerAlias('JDataSet', \Joomla\Data\DataSet::class, '6.0');
JLoader::registerAlias('JDataDumpable', \Joomla\Data\DumpableInterface::class, '6.0');

JLoader::registerAlias('JApplicationAdministrator', \Joomla\CMS\Application\AdministratorApplication::class, '6.0');
JLoader::registerAlias('JApplicationHelper', \Joomla\CMS\Application\ApplicationHelper::class, '6.0');
JLoader::registerAlias('JApplicationBase', \Joomla\CMS\Application\BaseApplication::class, '6.0');
JLoader::registerAlias('JApplicationCli', \Joomla\CMS\Application\CliApplication::class, '6.0');
JLoader::registerAlias('JApplicationCms', \Joomla\CMS\Application\CMSApplication::class, '6.0');
JLoader::registerAlias('JApplicationDaemon', \Joomla\CMS\Application\DaemonApplication::class, '6.0');
JLoader::registerAlias('JApplicationSite', \Joomla\CMS\Application\SiteApplication::class, '6.0');
JLoader::registerAlias('JApplicationWeb', \Joomla\CMS\Application\WebApplication::class, '6.0');
JLoader::registerAlias('JApplicationWebClient', \Joomla\Application\Web\WebClient::class, '6.0');
JLoader::registerAlias('JDaemon', \Joomla\CMS\Application\DaemonApplication::class, '6.0');
JLoader::registerAlias('JCli', \Joomla\CMS\Application\CliApplication::class, '6.0');
JLoader::registerAlias('JWeb', \Joomla\CMS\Application\WebApplication::class, '4.0');
JLoader::registerAlias('JWebClient', \Joomla\Application\Web\WebClient::class, '4.0');

JLoader::registerAlias('JModelAdmin', \Joomla\CMS\MVC\Model\AdminModel::class, '6.0');
JLoader::registerAlias('JModelForm', \Joomla\CMS\MVC\Model\FormModel::class, '6.0');
JLoader::registerAlias('JModelItem', \Joomla\CMS\MVC\Model\ItemModel::class, '6.0');
JLoader::registerAlias('JModelList', \Joomla\CMS\MVC\Model\ListModel::class, '6.0');
JLoader::registerAlias('JModelLegacy', \Joomla\CMS\MVC\Model\BaseDatabaseModel::class, '6.0');
JLoader::registerAlias('JViewCategories', \Joomla\CMS\MVC\View\CategoriesView::class, '6.0');
JLoader::registerAlias('JViewCategory', \Joomla\CMS\MVC\View\CategoryView::class, '6.0');
JLoader::registerAlias('JViewCategoryfeed', \Joomla\CMS\MVC\View\CategoryFeedView::class, '6.0');
JLoader::registerAlias('JViewLegacy', \Joomla\CMS\MVC\View\HtmlView::class, '6.0');
JLoader::registerAlias('JControllerAdmin', \Joomla\CMS\MVC\Controller\AdminController::class, '6.0');
JLoader::registerAlias('JControllerLegacy', \Joomla\CMS\MVC\Controller\BaseController::class, '6.0');
JLoader::registerAlias('JControllerForm', \Joomla\CMS\MVC\Controller\FormController::class, '6.0');
JLoader::registerAlias('JTableInterface', \Joomla\CMS\Table\TableInterface::class, '6.0');
JLoader::registerAlias('JTable', \Joomla\CMS\Table\Table::class, '6.0');
JLoader::registerAlias('JTableNested', \Joomla\CMS\Table\Nested::class, '6.0');
JLoader::registerAlias('JTableAsset', \Joomla\CMS\Table\Asset::class, '6.0');
JLoader::registerAlias('JTableExtension', \Joomla\CMS\Table\Extension::class, '6.0');
JLoader::registerAlias('JTableLanguage', \Joomla\CMS\Table\Language::class, '6.0');
JLoader::registerAlias('JTableUpdate', \Joomla\CMS\Table\Update::class, '6.0');
JLoader::registerAlias('JTableUpdatesite', \Joomla\CMS\Table\UpdateSite::class, '6.0');
JLoader::registerAlias('JTableUser', \Joomla\CMS\Table\User::class, '6.0');
JLoader::registerAlias('JTableUsergroup', \Joomla\CMS\Table\Usergroup::class, '6.0');
JLoader::registerAlias('JTableViewlevel', \Joomla\CMS\Table\ViewLevel::class, '6.0');
JLoader::registerAlias('JTableContenthistory', \Joomla\CMS\Table\ContentHistory::class, '6.0');
JLoader::registerAlias('JTableContenttype', \Joomla\CMS\Table\ContentType::class, '6.0');
JLoader::registerAlias('JTableCorecontent', \Joomla\CMS\Table\CoreContent::class, '6.0');
JLoader::registerAlias('JTableUcm', \Joomla\CMS\Table\Ucm::class, '6.0');
JLoader::registerAlias('JTableCategory', \Joomla\CMS\Table\Category::class, '6.0');
JLoader::registerAlias('JTableContent', \Joomla\CMS\Table\Content::class, '6.0');
JLoader::registerAlias('JTableMenu', \Joomla\CMS\Table\Menu::class, '6.0');
JLoader::registerAlias('JTableMenuType', \Joomla\CMS\Table\MenuType::class, '6.0');
JLoader::registerAlias('JTableModule', \Joomla\CMS\Table\Module::class, '6.0');

JLoader::registerAlias('JAccess', \Joomla\CMS\Access\Access::class, '6.0');
JLoader::registerAlias('JAccessRule', \Joomla\CMS\Access\Rule::class, '6.0');
JLoader::registerAlias('JAccessRules', \Joomla\CMS\Access\Rules::class, '6.0');
JLoader::registerAlias('JAccessExceptionNotallowed', \Joomla\CMS\Access\Exception\NotAllowed::class, '6.0');
JLoader::registerAlias('JRule', \Joomla\CMS\Access\Rule::class, '6.0');
JLoader::registerAlias('JRules', \Joomla\CMS\Access\Rules::class, '6.0');

JLoader::registerAlias('JHelp', \Joomla\CMS\Help\Help::class, '6.0');
JLoader::registerAlias('JCaptcha', \Joomla\CMS\Captcha\Captcha::class, '6.0');

JLoader::registerAlias('JLanguageAssociations', \Joomla\CMS\Language\Associations::class, '6.0');
JLoader::registerAlias('JLanguage', \Joomla\CMS\Language\Language::class, '6.0');
JLoader::registerAlias('JLanguageHelper', \Joomla\CMS\Language\LanguageHelper::class, '6.0');
JLoader::registerAlias('JLanguageMultilang', \Joomla\CMS\Language\Multilanguage::class, '6.0');
JLoader::registerAlias('JText', \Joomla\CMS\Language\Text::class, '6.0');
JLoader::registerAlias('JLanguageTransliterate', \Joomla\CMS\Language\Transliterate::class, '6.0');

JLoader::registerAlias('JComponentHelper', \Joomla\CMS\Component\ComponentHelper::class, '6.0');
JLoader::registerAlias('JComponentRecord', \Joomla\CMS\Component\ComponentRecord::class, '6.0');
JLoader::registerAlias('JComponentExceptionMissing', \Joomla\CMS\Component\Exception\MissingComponentException::class, '6.0');
JLoader::registerAlias('JComponentRouterBase', \Joomla\CMS\Component\Router\RouterBase::class, '6.0');
JLoader::registerAlias('JComponentRouterInterface', \Joomla\CMS\Component\Router\RouterInterface::class, '6.0');
JLoader::registerAlias('JComponentRouterLegacy', \Joomla\CMS\Component\Router\RouterLegacy::class, '6.0');
JLoader::registerAlias('JComponentRouterView', \Joomla\CMS\Component\Router\RouterView::class, '6.0');
JLoader::registerAlias('JComponentRouterViewconfiguration', \Joomla\CMS\Component\Router\RouterViewConfiguration::class, '6.0');
JLoader::registerAlias('JComponentRouterRulesMenu', \Joomla\CMS\Component\Router\Rules\MenuRules::class, '6.0');
JLoader::registerAlias('JComponentRouterRulesNomenu', \Joomla\CMS\Component\Router\Rules\NomenuRules::class, '6.0');
JLoader::registerAlias('JComponentRouterRulesInterface', \Joomla\CMS\Component\Router\Rules\RulesInterface::class, '6.0');
JLoader::registerAlias('JComponentRouterRulesStandard', \Joomla\CMS\Component\Router\Rules\StandardRules::class, '6.0');

JLoader::registerAlias('JEditor', \Joomla\CMS\Editor\Editor::class, '6.0');

JLoader::registerAlias('JErrorPage', \Joomla\CMS\Exception\ExceptionHandler::class, '6.0');

JLoader::registerAlias('JAuthenticationHelper', \Joomla\CMS\Helper\AuthenticationHelper::class, '6.0');
JLoader::registerAlias('JHelper', \Joomla\CMS\Helper\CMSHelper::class, '6.0');
JLoader::registerAlias('JHelperContent', \Joomla\CMS\Helper\ContentHelper::class, '6.0');
JLoader::registerAlias('JLibraryHelper', \Joomla\CMS\Helper\LibraryHelper::class, '6.0');
JLoader::registerAlias('JHelperMedia', \Joomla\CMS\Helper\MediaHelper::class, '6.0');
JLoader::registerAlias('JModuleHelper', \Joomla\CMS\Helper\ModuleHelper::class, '6.0');
JLoader::registerAlias('JHelperRoute', \Joomla\CMS\Helper\RouteHelper::class, '6.0');
JLoader::registerAlias('JHelperTags', \Joomla\CMS\Helper\TagsHelper::class, '6.0');
JLoader::registerAlias('JHelperUsergroups', \Joomla\CMS\Helper\UserGroupsHelper::class, '6.0');

JLoader::registerAlias('JLayoutBase', \Joomla\CMS\Layout\BaseLayout::class, '6.0');
JLoader::registerAlias('JLayoutFile', \Joomla\CMS\Layout\FileLayout::class, '6.0');
JLoader::registerAlias('JLayoutHelper', \Joomla\CMS\Layout\LayoutHelper::class, '6.0');
JLoader::registerAlias('JLayout', \Joomla\CMS\Layout\LayoutInterface::class, '6.0');

JLoader::registerAlias('JResponseJson', \Joomla\CMS\Response\JsonResponse::class, '6.0');

JLoader::registerAlias('JPlugin', \Joomla\CMS\Plugin\CMSPlugin::class, '6.0');
JLoader::registerAlias('JPluginHelper', \Joomla\CMS\Plugin\PluginHelper::class, '6.0');

JLoader::registerAlias('JMenu', \Joomla\CMS\Menu\AbstractMenu::class, '6.0');
JLoader::registerAlias('JMenuAdministrator', \Joomla\CMS\Menu\AdministratorMenu::class, '6.0');
JLoader::registerAlias('JMenuItem', \Joomla\CMS\Menu\MenuItem::class, '6.0');
JLoader::registerAlias('JMenuSite', \Joomla\CMS\Menu\SiteMenu::class, '6.0');

JLoader::registerAlias('JPagination', \Joomla\CMS\Pagination\Pagination::class, '6.0');
JLoader::registerAlias('JPaginationObject', \Joomla\CMS\Pagination\PaginationObject::class, '6.0');

JLoader::registerAlias('JPathway', \Joomla\CMS\Pathway\Pathway::class, '6.0');
JLoader::registerAlias('JPathwaySite', \Joomla\CMS\Pathway\SitePathway::class, '6.0');

JLoader::registerAlias('JSchemaChangeitem', \Joomla\CMS\Schema\ChangeItem::class, '6.0');
JLoader::registerAlias('JSchemaChangeset', \Joomla\CMS\Schema\ChangeSet::class, '6.0');
JLoader::registerAlias('JSchemaChangeitemMysql', \Joomla\CMS\Schema\ChangeItem\MysqlChangeItem::class, '6.0');
JLoader::registerAlias('JSchemaChangeitemPostgresql', \Joomla\CMS\Schema\ChangeItem\PostgresqlChangeItem::class, '6.0');

JLoader::registerAlias('JUcm', \Joomla\CMS\UCM\UCM::class, '6.0');
JLoader::registerAlias('JUcmBase', \Joomla\CMS\UCM\UCMBase::class, '6.0');
JLoader::registerAlias('JUcmContent', \Joomla\CMS\UCM\UCMContent::class, '6.0');
JLoader::registerAlias('JUcmType', \Joomla\CMS\UCM\UCMType::class, '6.0');

JLoader::registerAlias('JToolbar', \Joomla\CMS\Toolbar\Toolbar::class, '6.0');
JLoader::registerAlias('JToolbarButton', \Joomla\CMS\Toolbar\ToolbarButton::class, '6.0');
JLoader::registerAlias('JToolbarButtonConfirm', \Joomla\CMS\Toolbar\Button\ConfirmButton::class, '6.0');
JLoader::registerAlias('JToolbarButtonCustom', \Joomla\CMS\Toolbar\Button\CustomButton::class, '6.0');
JLoader::registerAlias('JToolbarButtonHelp', \Joomla\CMS\Toolbar\Button\HelpButton::class, '6.0');
JLoader::registerAlias('JToolbarButtonLink', \Joomla\CMS\Toolbar\Button\LinkButton::class, '6.0');
JLoader::registerAlias('JToolbarButtonPopup', \Joomla\CMS\Toolbar\Button\PopupButton::class, '6.0');
JLoader::registerAlias('JToolbarButtonSeparator', \Joomla\CMS\Toolbar\Button\SeparatorButton::class, '6.0');
JLoader::registerAlias('JToolbarButtonStandard', \Joomla\CMS\Toolbar\Button\StandardButton::class, '6.0');
JLoader::registerAlias('JToolbarHelper', \Joomla\CMS\Toolbar\ToolbarHelper::class, '6.0');
JLoader::registerAlias('JButton', \Joomla\CMS\Toolbar\ToolbarButton::class, '6.0');

JLoader::registerAlias('JVersion', \Joomla\CMS\Version::class, '6.0');

JLoader::registerAlias('JAuthentication', \Joomla\CMS\Authentication\Authentication::class, '6.0');
JLoader::registerAlias('JAuthenticationResponse', \Joomla\CMS\Authentication\AuthenticationResponse::class, '6.0');

JLoader::registerAlias('JBrowser', \Joomla\CMS\Environment\Browser::class, '6.0');

JLoader::registerAlias('JAssociationExtensionInterface', \Joomla\CMS\Association\AssociationExtensionInterface::class, '6.0');
JLoader::registerAlias('JAssociationExtensionHelper', \Joomla\CMS\Association\AssociationExtensionHelper::class, '6.0');

JLoader::registerAlias('JDocument', \Joomla\CMS\Document\Document::class, '6.0');
JLoader::registerAlias('JDocumentError', \Joomla\CMS\Document\ErrorDocument::class, '6.0');
JLoader::registerAlias('JDocumentFeed', \Joomla\CMS\Document\FeedDocument::class, '6.0');
JLoader::registerAlias('JDocumentHtml', \Joomla\CMS\Document\HtmlDocument::class, '6.0');
JLoader::registerAlias('JDocumentImage', \Joomla\CMS\Document\ImageDocument::class, '6.0');
JLoader::registerAlias('JDocumentJson', \Joomla\CMS\Document\JsonDocument::class, '6.0');
JLoader::registerAlias('JDocumentOpensearch', \Joomla\CMS\Document\OpensearchDocument::class, '6.0');
JLoader::registerAlias('JDocumentRaw', \Joomla\CMS\Document\RawDocument::class, '6.0');
JLoader::registerAlias('JDocumentRenderer', \Joomla\CMS\Document\DocumentRenderer::class, '6.0');
JLoader::registerAlias('JDocumentXml', \Joomla\CMS\Document\XmlDocument::class, '6.0');
JLoader::registerAlias('JDocumentRendererFeedAtom', \Joomla\CMS\Document\Renderer\Feed\AtomRenderer::class, '6.0');
JLoader::registerAlias('JDocumentRendererFeedRss', \Joomla\CMS\Document\Renderer\Feed\RssRenderer::class, '6.0');
JLoader::registerAlias('JDocumentRendererHtmlComponent', \Joomla\CMS\Document\Renderer\Html\ComponentRenderer::class, '6.0');
JLoader::registerAlias('JDocumentRendererHtmlHead', \Joomla\CMS\Document\Renderer\Html\HeadRenderer::class, '6.0');
JLoader::registerAlias('JDocumentRendererHtmlMessage', \Joomla\CMS\Document\Renderer\Html\MessageRenderer::class, '6.0');
JLoader::registerAlias('JDocumentRendererHtmlModule', \Joomla\CMS\Document\Renderer\Html\ModuleRenderer::class, '6.0');
JLoader::registerAlias('JDocumentRendererHtmlModules', \Joomla\CMS\Document\Renderer\Html\ModulesRenderer::class, '6.0');
JLoader::registerAlias('JDocumentRendererAtom', \Joomla\CMS\Document\Renderer\Feed\AtomRenderer::class, '4.0');
JLoader::registerAlias('JDocumentRendererRSS', \Joomla\CMS\Document\Renderer\Feed\RssRenderer::class, '4.0');
JLoader::registerAlias('JDocumentRendererComponent', \Joomla\CMS\Document\Renderer\Html\ComponentRenderer::class, '4.0');
JLoader::registerAlias('JDocumentRendererHead', \Joomla\CMS\Document\Renderer\Html\HeadRenderer::class, '4.0');
JLoader::registerAlias('JDocumentRendererMessage', \Joomla\CMS\Document\Renderer\Html\MessageRenderer::class, '4.0');
JLoader::registerAlias('JDocumentRendererModule', \Joomla\CMS\Document\Renderer\Html\ModuleRenderer::class, '4.0');
JLoader::registerAlias('JDocumentRendererModules', \Joomla\CMS\Document\Renderer\Html\ModulesRenderer::class, '4.0');
JLoader::registerAlias('JFeedEnclosure', \Joomla\CMS\Document\Feed\FeedEnclosure::class, '6.0');
JLoader::registerAlias('JFeedImage', \Joomla\CMS\Document\Feed\FeedImage::class, '6.0');
JLoader::registerAlias('JFeedItem', \Joomla\CMS\Document\Feed\FeedItem::class, '6.0');
JLoader::registerAlias('JOpenSearchImage', \Joomla\CMS\Document\Opensearch\OpensearchImage::class, '6.0');
JLoader::registerAlias('JOpenSearchUrl', \Joomla\CMS\Document\Opensearch\OpensearchUrl::class, '6.0');

JLoader::registerAlias('JFilterInput', \Joomla\CMS\Filter\InputFilter::class, '6.0');
JLoader::registerAlias('JFilterOutput', \Joomla\CMS\Filter\OutputFilter::class, '6.0');

JLoader::registerAlias('JHttp', \Joomla\CMS\Http\Http::class, '6.0');
JLoader::registerAlias('JHttpFactory', \Joomla\CMS\Http\HttpFactory::class, '6.0');
JLoader::registerAlias('JHttpResponse', \Joomla\CMS\Http\Response::class, '6.0');
JLoader::registerAlias('JHttpTransport', \Joomla\CMS\Http\TransportInterface::class, '6.0');
JLoader::registerAlias('JHttpTransportCurl', \Joomla\CMS\Http\Transport\CurlTransport::class, '6.0');
JLoader::registerAlias('JHttpTransportSocket', \Joomla\CMS\Http\Transport\SocketTransport::class, '6.0');
JLoader::registerAlias('JHttpTransportStream', \Joomla\CMS\Http\Transport\StreamTransport::class, '6.0');

JLoader::registerAlias('JInstaller', \Joomla\CMS\Installer\Installer::class, '6.0');
JLoader::registerAlias('JInstallerAdapter', \Joomla\CMS\Installer\InstallerAdapter::class, '6.0');
JLoader::registerAlias('JInstallerExtension', \Joomla\CMS\Installer\InstallerExtension::class, '6.0');
JLoader::registerAlias('JExtension', \Joomla\CMS\Installer\InstallerExtension::class, '6.0');
JLoader::registerAlias('JInstallerHelper', \Joomla\CMS\Installer\InstallerHelper::class, '6.0');
JLoader::registerAlias('JInstallerScript', \Joomla\CMS\Installer\InstallerScript::class, '6.0');
JLoader::registerAlias('JInstallerManifest', \Joomla\CMS\Installer\Manifest::class, '6.0');
JLoader::registerAlias('JInstallerAdapterComponent', \Joomla\CMS\Installer\Adapter\ComponentAdapter::class, '6.0');
JLoader::registerAlias('JInstallerComponent', \Joomla\CMS\Installer\Adapter\ComponentAdapter::class, '6.0');
JLoader::registerAlias('JInstallerAdapterFile', \Joomla\CMS\Installer\Adapter\FileAdapter::class, '6.0');
JLoader::registerAlias('JInstallerFile', \Joomla\CMS\Installer\Adapter\FileAdapter::class, '6.0');
JLoader::registerAlias('JInstallerAdapterLanguage', \Joomla\CMS\Installer\Adapter\LanguageAdapter::class, '6.0');
JLoader::registerAlias('JInstallerLanguage', \Joomla\CMS\Installer\Adapter\LanguageAdapter::class, '6.0');
JLoader::registerAlias('JInstallerAdapterLibrary', \Joomla\CMS\Installer\Adapter\LibraryAdapter::class, '6.0');
JLoader::registerAlias('JInstallerLibrary', \Joomla\CMS\Installer\Adapter\LibraryAdapter::class, '6.0');
JLoader::registerAlias('JInstallerAdapterModule', \Joomla\CMS\Installer\Adapter\ModuleAdapter::class, '6.0');
JLoader::registerAlias('JInstallerModule', \Joomla\CMS\Installer\Adapter\ModuleAdapter::class, '6.0');
JLoader::registerAlias('JInstallerAdapterPackage', \Joomla\CMS\Installer\Adapter\PackageAdapter::class, '6.0');
JLoader::registerAlias('JInstallerPackage', \Joomla\CMS\Installer\Adapter\PackageAdapter::class, '6.0');
JLoader::registerAlias('JInstallerAdapterPlugin', \Joomla\CMS\Installer\Adapter\PluginAdapter::class, '6.0');
JLoader::registerAlias('JInstallerPlugin', \Joomla\CMS\Installer\Adapter\PluginAdapter::class, '6.0');
JLoader::registerAlias('JInstallerAdapterTemplate', \Joomla\CMS\Installer\Adapter\TemplateAdapter::class, '6.0');
JLoader::registerAlias('JInstallerTemplate', \Joomla\CMS\Installer\Adapter\TemplateAdapter::class, '6.0');
JLoader::registerAlias('JInstallerManifestLibrary', \Joomla\CMS\Installer\Manifest\LibraryManifest::class, '6.0');
JLoader::registerAlias('JInstallerManifestPackage', \Joomla\CMS\Installer\Manifest\PackageManifest::class, '6.0');

JLoader::registerAlias('JRouterAdministrator', \Joomla\CMS\Router\AdministratorRouter::class, '6.0');
JLoader::registerAlias('JRoute', \Joomla\CMS\Router\Route::class, '6.0');
JLoader::registerAlias('JRouter', \Joomla\CMS\Router\Router::class, '6.0');
JLoader::registerAlias('JRouterSite', \Joomla\CMS\Router\SiteRouter::class, '6.0');

JLoader::registerAlias('JCategories', \Joomla\CMS\Categories\Categories::class, '6.0');
JLoader::registerAlias('JCategoryNode', \Joomla\CMS\Categories\CategoryNode::class, '6.0');

JLoader::registerAlias('JDate', \Joomla\CMS\Date\Date::class, '6.0');

JLoader::registerAlias('JLog', \Joomla\CMS\Log\Log::class, '6.0');
JLoader::registerAlias('JLogEntry', \Joomla\CMS\Log\LogEntry::class, '6.0');
JLoader::registerAlias('JLogLogger', \Joomla\CMS\Log\Logger::class, '6.0');
JLoader::registerAlias('JLogger', \Joomla\CMS\Log\Logger::class, '6.0');
JLoader::registerAlias('JLogLoggerCallback', \Joomla\CMS\Log\Logger\CallbackLogger::class, '6.0');
JLoader::registerAlias('JLogLoggerDatabase', \Joomla\CMS\Log\Logger\DatabaseLogger::class, '6.0');
JLoader::registerAlias('JLogLoggerEcho', \Joomla\CMS\Log\Logger\EchoLogger::class, '6.0');
JLoader::registerAlias('JLogLoggerFormattedtext', \Joomla\CMS\Log\Logger\FormattedtextLogger::class, '6.0');
JLoader::registerAlias('JLogLoggerMessagequeue', \Joomla\CMS\Log\Logger\MessagequeueLogger::class, '6.0');
JLoader::registerAlias('JLogLoggerSyslog', \Joomla\CMS\Log\Logger\SyslogLogger::class, '6.0');
JLoader::registerAlias('JLogLoggerW3c', \Joomla\CMS\Log\Logger\W3cLogger::class, '6.0');

JLoader::registerAlias('JProfiler', \Joomla\CMS\Profiler\Profiler::class, '6.0');

JLoader::registerAlias('JUri', \Joomla\CMS\Uri\Uri::class, '6.0');

JLoader::registerAlias('JCache', \Joomla\CMS\Cache\Cache::class, '6.0');
JLoader::registerAlias('JCacheController', \Joomla\CMS\Cache\CacheController::class, '6.0');
JLoader::registerAlias('JCacheStorage', \Joomla\CMS\Cache\CacheStorage::class, '6.0');
JLoader::registerAlias('JCacheControllerCallback', \Joomla\CMS\Cache\Controller\CallbackController::class, '6.0');
JLoader::registerAlias('JCacheControllerOutput', \Joomla\CMS\Cache\Controller\OutputController::class, '6.0');
JLoader::registerAlias('JCacheControllerPage', \Joomla\CMS\Cache\Controller\PageController::class, '6.0');
JLoader::registerAlias('JCacheControllerView', \Joomla\CMS\Cache\Controller\ViewController::class, '6.0');
JLoader::registerAlias('JCacheStorageApcu', \Joomla\CMS\Cache\Storage\ApcuStorage::class, '6.0');
JLoader::registerAlias('JCacheStorageHelper', \Joomla\CMS\Cache\Storage\CacheStorageHelper::class, '6.0');
JLoader::registerAlias('JCacheStorageFile', \Joomla\CMS\Cache\Storage\FileStorage::class, '6.0');
JLoader::registerAlias('JCacheStorageMemcached', \Joomla\CMS\Cache\Storage\MemcachedStorage::class, '6.0');
JLoader::registerAlias('JCacheStorageRedis', \Joomla\CMS\Cache\Storage\RedisStorage::class, '6.0');
JLoader::registerAlias('JCacheException', \Joomla\CMS\Cache\Exception\CacheExceptionInterface::class, '6.0');
JLoader::registerAlias('JCacheExceptionConnecting', \Joomla\CMS\Cache\Exception\CacheConnectingException::class, '6.0');
JLoader::registerAlias('JCacheExceptionUnsupported', \Joomla\CMS\Cache\Exception\UnsupportedCacheException::class, '6.0');

JLoader::registerAlias('JSession', \Joomla\CMS\Session\Session::class, '6.0');

JLoader::registerAlias('JUser', \Joomla\CMS\User\User::class, '6.0');
JLoader::registerAlias('JUserHelper', \Joomla\CMS\User\UserHelper::class, '6.0');

JLoader::registerAlias('JForm', \Joomla\CMS\Form\Form::class, '6.0');
JLoader::registerAlias('JFormField', \Joomla\CMS\Form\FormField::class, '6.0');
JLoader::registerAlias('JFormHelper', \Joomla\CMS\Form\FormHelper::class, '6.0');
JLoader::registerAlias('JFormRule', \Joomla\CMS\Form\FormRule::class, '6.0');

JLoader::registerAlias('JFormFieldAccessLevel', \Joomla\CMS\Form\Field\AccesslevelField::class, '6.0');
JLoader::registerAlias('JFormFieldAliastag', \Joomla\CMS\Form\Field\AliastagField::class, '6.0');
JLoader::registerAlias('JFormFieldAuthor', \Joomla\CMS\Form\Field\AuthorField::class, '6.0');
JLoader::registerAlias('JFormFieldCacheHandler', \Joomla\CMS\Form\Field\CachehandlerField::class, '6.0');
JLoader::registerAlias('JFormFieldCalendar', \Joomla\CMS\Form\Field\CalendarField::class, '6.0');
JLoader::registerAlias('JFormFieldCaptcha', \Joomla\CMS\Form\Field\CaptchaField::class, '6.0');
JLoader::registerAlias('JFormFieldCategory', \Joomla\CMS\Form\Field\CategoryField::class, '6.0');
JLoader::registerAlias('JFormFieldCheckbox', \Joomla\CMS\Form\Field\CheckboxField::class, '6.0');
JLoader::registerAlias('JFormFieldCheckboxes', \Joomla\CMS\Form\Field\CheckboxesField::class, '6.0');
JLoader::registerAlias('JFormFieldChromeStyle', \Joomla\CMS\Form\Field\ChromestyleField::class, '6.0');
JLoader::registerAlias('JFormFieldColor', \Joomla\CMS\Form\Field\ColorField::class, '6.0');
JLoader::registerAlias('JFormFieldCombo', \Joomla\CMS\Form\Field\ComboField::class, '6.0');
JLoader::registerAlias('JFormFieldComponentlayout', \Joomla\CMS\Form\Field\ComponentlayoutField::class, '6.0');
JLoader::registerAlias('JFormFieldComponents', \Joomla\CMS\Form\Field\ComponentsField::class, '6.0');
JLoader::registerAlias('JFormFieldContenthistory', \Joomla\CMS\Form\Field\ContenthistoryField::class, '6.0');
JLoader::registerAlias('JFormFieldContentlanguage', \Joomla\CMS\Form\Field\ContentlanguageField::class, '6.0');
JLoader::registerAlias('JFormFieldContenttype', \Joomla\CMS\Form\Field\ContenttypeField::class, '6.0');
JLoader::registerAlias('JFormFieldDatabaseConnection', \Joomla\CMS\Form\Field\DatabaseconnectionField::class, '6.0');
JLoader::registerAlias('JFormFieldEditor', \Joomla\CMS\Form\Field\EditorField::class, '6.0');
JLoader::registerAlias('JFormFieldEMail', \Joomla\CMS\Form\Field\EmailField::class, '6.0');
JLoader::registerAlias('JFormFieldFile', \Joomla\CMS\Form\Field\FileField::class, '6.0');
JLoader::registerAlias('JFormFieldFileList', \Joomla\CMS\Form\Field\FilelistField::class, '6.0');
JLoader::registerAlias('JFormFieldFolderList', \Joomla\CMS\Form\Field\FolderlistField::class, '6.0');
JLoader::registerAlias('JFormFieldFrontend_Language', \Joomla\CMS\Form\Field\FrontendlanguageField::class, '6.0');
JLoader::registerAlias('JFormFieldGroupedList', \Joomla\CMS\Form\Field\GroupedlistField::class, '6.0');
JLoader::registerAlias('JFormFieldHeadertag', \Joomla\CMS\Form\Field\HeadertagField::class, '6.0');
JLoader::registerAlias('JFormFieldHidden', \Joomla\CMS\Form\Field\HiddenField::class, '6.0');
JLoader::registerAlias('JFormFieldImageList', \Joomla\CMS\Form\Field\ImagelistField::class, '6.0');
JLoader::registerAlias('JFormFieldInteger', \Joomla\CMS\Form\Field\IntegerField::class, '6.0');
JLoader::registerAlias('JFormFieldLanguage', \Joomla\CMS\Form\Field\LanguageField::class, '6.0');
JLoader::registerAlias('JFormFieldLastvisitDateRange', \Joomla\CMS\Form\Field\LastvisitdaterangeField::class, '6.0');
JLoader::registerAlias('JFormFieldLimitbox', \Joomla\CMS\Form\Field\LimitboxField::class, '6.0');
JLoader::registerAlias('JFormFieldList', \Joomla\CMS\Form\Field\ListField::class, '6.0');
JLoader::registerAlias('JFormFieldMedia', \Joomla\CMS\Form\Field\MediaField::class, '6.0');
JLoader::registerAlias('JFormFieldMenu', \Joomla\CMS\Form\Field\MenuField::class, '6.0');
JLoader::registerAlias('JFormFieldMenuitem', \Joomla\CMS\Form\Field\MenuitemField::class, '6.0');
JLoader::registerAlias('JFormFieldMeter', \Joomla\CMS\Form\Field\MeterField::class, '6.0');
JLoader::registerAlias('JFormFieldModulelayout', \Joomla\CMS\Form\Field\ModulelayoutField::class, '6.0');
JLoader::registerAlias('JFormFieldModuleOrder', \Joomla\CMS\Form\Field\ModuleorderField::class, '6.0');
JLoader::registerAlias('JFormFieldModulePosition', \Joomla\CMS\Form\Field\ModulepositionField::class, '6.0');
JLoader::registerAlias('JFormFieldModuletag', \Joomla\CMS\Form\Field\ModuletagField::class, '6.0');
JLoader::registerAlias('JFormFieldNote', \Joomla\CMS\Form\Field\NoteField::class, '6.0');
JLoader::registerAlias('JFormFieldNumber', \Joomla\CMS\Form\Field\NumberField::class, '6.0');
JLoader::registerAlias('JFormFieldOrdering', \Joomla\CMS\Form\Field\OrderingField::class, '6.0');
JLoader::registerAlias('JFormFieldPassword', \Joomla\CMS\Form\Field\PasswordField::class, '6.0');
JLoader::registerAlias('JFormFieldPlugins', \Joomla\CMS\Form\Field\PluginsField::class, '6.0');
JLoader::registerAlias('JFormFieldPlugin_Status', \Joomla\CMS\Form\Field\PluginstatusField::class, '6.0');
JLoader::registerAlias('JFormFieldPredefinedList', \Joomla\CMS\Form\Field\PredefinedListField::class, '6.0');
JLoader::registerAlias('JFormFieldRadio', \Joomla\CMS\Form\Field\RadioField::class, '6.0');
JLoader::registerAlias('JFormFieldRange', \Joomla\CMS\Form\Field\RangeField::class, '6.0');
JLoader::registerAlias('JFormFieldRedirect_Status', \Joomla\CMS\Form\Field\RedirectStatusField::class, '6.0');
JLoader::registerAlias('JFormFieldRegistrationDateRange', \Joomla\CMS\Form\Field\RegistrationdaterangeField::class, '6.0');
JLoader::registerAlias('JFormFieldRules', \Joomla\CMS\Form\Field\RulesField::class, '6.0');
JLoader::registerAlias('JFormFieldSessionHandler', \Joomla\CMS\Form\Field\SessionhandlerField::class, '6.0');
JLoader::registerAlias('JFormFieldSpacer', \Joomla\CMS\Form\Field\SpacerField::class, '6.0');
JLoader::registerAlias('JFormFieldSQL', \Joomla\CMS\Form\Field\SqlField::class, '6.0');
JLoader::registerAlias('JFormFieldStatus', \Joomla\CMS\Form\Field\StatusField::class, '6.0');
JLoader::registerAlias('JFormFieldSubform', \Joomla\CMS\Form\Field\SubformField::class, '6.0');
JLoader::registerAlias('JFormFieldTag', \Joomla\CMS\Form\Field\TagField::class, '6.0');
JLoader::registerAlias('JFormFieldTel', \Joomla\CMS\Form\Field\TelephoneField::class, '6.0');
JLoader::registerAlias('JFormFieldTemplatestyle', \Joomla\CMS\Form\Field\TemplatestyleField::class, '6.0');
JLoader::registerAlias('JFormFieldText', \Joomla\CMS\Form\Field\TextField::class, '6.0');
JLoader::registerAlias('JFormFieldTextarea', \Joomla\CMS\Form\Field\TextareaField::class, '6.0');
JLoader::registerAlias('JFormFieldTimezone', \Joomla\CMS\Form\Field\TimezoneField::class, '6.0');
JLoader::registerAlias('JFormFieldUrl', \Joomla\CMS\Form\Field\UrlField::class, '6.0');
JLoader::registerAlias('JFormFieldUserActive', \Joomla\CMS\Form\Field\UseractiveField::class, '6.0');
JLoader::registerAlias('JFormFieldUserGroupList', \Joomla\CMS\Form\Field\UsergrouplistField::class, '6.0');
JLoader::registerAlias('JFormFieldUserState', \Joomla\CMS\Form\Field\UserstateField::class, '6.0');
JLoader::registerAlias('JFormFieldUser', \Joomla\CMS\Form\Field\UserField::class, '6.0');
JLoader::registerAlias('JFormRuleBoolean', \Joomla\CMS\Form\Rule\BooleanRule::class, '6.0');
JLoader::registerAlias('JFormRuleCalendar', \Joomla\CMS\Form\Rule\CalendarRule::class, '6.0');
JLoader::registerAlias('JFormRuleCaptcha', \Joomla\CMS\Form\Rule\CaptchaRule::class, '6.0');
JLoader::registerAlias('JFormRuleColor', \Joomla\CMS\Form\Rule\ColorRule::class, '6.0');
JLoader::registerAlias('JFormRuleEmail', \Joomla\CMS\Form\Rule\EmailRule::class, '6.0');
JLoader::registerAlias('JFormRuleEquals', \Joomla\CMS\Form\Rule\EqualsRule::class, '6.0');
JLoader::registerAlias('JFormRuleNotequals', \Joomla\CMS\Form\Rule\NotequalsRule::class, '6.0');
JLoader::registerAlias('JFormRuleNumber', \Joomla\CMS\Form\Rule\NumberRule::class, '6.0');
JLoader::registerAlias('JFormRuleOptions', \Joomla\CMS\Form\Rule\OptionsRule::class, '6.0');
JLoader::registerAlias('JFormRulePassword', \Joomla\CMS\Form\Rule\PasswordRule::class, '6.0');
JLoader::registerAlias('JFormRuleRules', \Joomla\CMS\Form\Rule\RulesRule::class, '6.0');
JLoader::registerAlias('JFormRuleTel', \Joomla\CMS\Form\Rule\TelRule::class, '6.0');
JLoader::registerAlias('JFormRuleUrl', \Joomla\CMS\Form\Rule\UrlRule::class, '6.0');
JLoader::registerAlias('JFormRuleUsername', \Joomla\CMS\Form\Rule\UsernameRule::class, '6.0');

JLoader::registerAlias('JMicrodata', \Joomla\CMS\Microdata\Microdata::class, '6.0');

JLoader::registerAlias('JDatabaseDriver', \Joomla\Database\DatabaseDriver::class, '6.0');
JLoader::registerAlias('JDatabaseExporter', \Joomla\Database\DatabaseExporter::class, '6.0');
JLoader::registerAlias('JDatabaseFactory', \Joomla\Database\DatabaseFactory::class, '6.0');
JLoader::registerAlias('JDatabaseImporter', \Joomla\Database\DatabaseImporter::class, '6.0');
JLoader::registerAlias('JDatabaseInterface', \Joomla\Database\DatabaseInterface::class, '6.0');
JLoader::registerAlias('JDatabaseIterator', \Joomla\Database\DatabaseIterator::class, '6.0');
JLoader::registerAlias('JDatabaseQuery', \Joomla\Database\DatabaseQuery::class, '6.0');
JLoader::registerAlias('JDatabaseDriverMysqli', \Joomla\Database\Mysqli\MysqliDriver::class, '6.0');
JLoader::registerAlias('JDatabaseDriverPdo', \Joomla\Database\Pdo\PdoDriver::class, '6.0');
JLoader::registerAlias('JDatabaseDriverPdomysql', \Joomla\Database\Mysql\MysqlDriver::class, '6.0');
JLoader::registerAlias('JDatabaseDriverPgsql', \Joomla\Database\Pgsql\PgsqlDriver::class, '6.0');
JLoader::registerAlias('JDatabaseDriverSqlazure', \Joomla\Database\Sqlazure\SqlazureDriver::class, '6.0');
JLoader::registerAlias('JDatabaseDriverSqlite', \Joomla\Database\Sqlite\SqliteDriver::class, '6.0');
JLoader::registerAlias('JDatabaseDriverSqlsrv', \Joomla\Database\Sqlsrv\SqlsrvDriver::class, '6.0');
JLoader::registerAlias('JDatabaseExceptionConnecting', \Joomla\Database\Exception\ConnectionFailureException::class, '6.0');
JLoader::registerAlias('JDatabaseExceptionExecuting', \Joomla\Database\Exception\ExecutionFailureException::class, '6.0');
JLoader::registerAlias('JDatabaseExceptionUnsupported', \Joomla\Database\Exception\UnsupportedAdapterException::class, '6.0');
JLoader::registerAlias('JDatabaseExporterMysqli', \Joomla\Database\Mysqli\MysqliExporter::class, '6.0');
JLoader::registerAlias('JDatabaseExporterPdomysql', \Joomla\Database\Mysql\MysqlExporter::class, '6.0');
JLoader::registerAlias('JDatabaseExporterPgsql', \Joomla\Database\Pgsql\PgsqlExporter::class, '6.0');
JLoader::registerAlias('JDatabaseImporterMysqli', \Joomla\Database\Mysqli\MysqliImporter::class, '6.0');
JLoader::registerAlias('JDatabaseImporterPdomysql', \Joomla\Database\Mysql\MysqlImporter::class, '6.0');
JLoader::registerAlias('JDatabaseImporterPgsql', \Joomla\Database\Pgsql\PgsqlImporter::class, '6.0');
JLoader::registerAlias('JDatabaseQueryElement', \Joomla\Database\Query\QueryElement::class, '6.0');
JLoader::registerAlias('JDatabaseQueryLimitable', \Joomla\Database\Query\LimitableInterface::class, '6.0');
JLoader::registerAlias('JDatabaseQueryPreparable', \Joomla\Database\Query\PreparableInterface::class, '6.0');
JLoader::registerAlias('JDatabaseQueryMysqli', \Joomla\Database\Mysqli\MysqliQuery::class, '6.0');
JLoader::registerAlias('JDatabaseQueryPdo', \Joomla\Database\Pdo\PdoQuery::class, '6.0');
JLoader::registerAlias('JDatabaseQueryPdomysql', \Joomla\Database\Mysql\MysqlQuery::class, '6.0');
JLoader::registerAlias('JDatabaseQueryPgsql', \Joomla\Database\Pgsql\PgsqlQuery::class, '6.0');
JLoader::registerAlias('JDatabaseQuerySqlazure', \Joomla\Database\Sqlazure\SqlazureQuery::class, '6.0');
JLoader::registerAlias('JDatabaseQuerySqlite', \Joomla\Database\Sqlite\SqliteQuery::class, '6.0');
JLoader::registerAlias('JDatabaseQuerySqlsrv', \Joomla\Database\Sqlsrv\SqlsrvQuery::class, '6.0');

JLoader::registerAlias('JFactory', \Joomla\CMS\Factory::class, '6.0');

JLoader::registerAlias('JMail', \Joomla\CMS\Mail\Mail::class, '6.0');
JLoader::registerAlias('JMailHelper', \Joomla\CMS\Mail\MailHelper::class, '6.0');

JLoader::registerAlias('JClientHelper', \Joomla\CMS\Client\ClientHelper::class, '6.0');
JLoader::registerAlias('JClientFtp', \Joomla\CMS\Client\FtpClient::class, '6.0');
JLoader::registerAlias('JFTP', \Joomla\CMS\Client\FtpClient::class, '4.0');

JLoader::registerAlias('JUpdate', \Joomla\CMS\Updater\Update::class, '6.0');
JLoader::registerAlias('JUpdateAdapter', \Joomla\CMS\Updater\UpdateAdapter::class, '6.0');
JLoader::registerAlias('JUpdater', \Joomla\CMS\Updater\Updater::class, '6.0');
JLoader::registerAlias('JUpdaterCollection', \Joomla\CMS\Updater\Adapter\CollectionAdapter::class, '6.0');
JLoader::registerAlias('JUpdaterExtension', \Joomla\CMS\Updater\Adapter\ExtensionAdapter::class, '6.0');

JLoader::registerAlias('JCrypt', \Joomla\CMS\Crypt\Crypt::class, '6.0');
JLoader::registerAlias('JCryptCipher', \Joomla\Crypt\CipherInterface::class, '6.0');
JLoader::registerAlias('JCryptKey', \Joomla\Crypt\Key::class, '6.0');
JLoader::registerAlias('\\Joomla\\CMS\\Crypt\\CipherInterface', \Joomla\Crypt\CipherInterface::class, '6.0');
JLoader::registerAlias('\\Joomla\\CMS\\Crypt\\Key', \Joomla\Crypt\Key::class, '6.0');
JLoader::registerAlias('JCryptCipherCrypto', \Joomla\CMS\Crypt\Cipher\CryptoCipher::class, '6.0');

JLoader::registerAlias('JStringPunycode', \Joomla\CMS\String\PunycodeHelper::class, '6.0');

JLoader::registerAlias('JBuffer', \Joomla\CMS\Utility\BufferStreamHandler::class, '6.0');
JLoader::registerAlias('JUtility', \Joomla\CMS\Utility\Utility::class, '6.0');

JLoader::registerAlias('JInputCli', \Joomla\CMS\Input\Cli::class, '6.0');
JLoader::registerAlias('JInputCookie', \Joomla\CMS\Input\Cookie::class, '6.0');
JLoader::registerAlias('JInputFiles', \Joomla\CMS\Input\Files::class, '6.0');
JLoader::registerAlias('JInput', \Joomla\CMS\Input\Input::class, '6.0');
JLoader::registerAlias('JInputJSON', \Joomla\CMS\Input\Json::class, '6.0');

JLoader::registerAlias('JFeed', \Joomla\CMS\Feed\Feed::class, '6.0');
JLoader::registerAlias('JFeedEntry', \Joomla\CMS\Feed\FeedEntry::class, '6.0');
JLoader::registerAlias('JFeedFactory', \Joomla\CMS\Feed\FeedFactory::class, '6.0');
JLoader::registerAlias('JFeedLink', \Joomla\CMS\Feed\FeedLink::class, '6.0');
JLoader::registerAlias('JFeedParser', \Joomla\CMS\Feed\FeedParser::class, '6.0');
JLoader::registerAlias('JFeedPerson', \Joomla\CMS\Feed\FeedPerson::class, '6.0');
JLoader::registerAlias('JFeedParserAtom', \Joomla\CMS\Feed\Parser\AtomParser::class, '6.0');
JLoader::registerAlias('JFeedParserNamespace', \Joomla\CMS\Feed\Parser\NamespaceParserInterface::class, '6.0');
JLoader::registerAlias('JFeedParserRss', \Joomla\CMS\Feed\Parser\RssParser::class, '6.0');
JLoader::registerAlias('JFeedParserRssItunes', \Joomla\CMS\Feed\Parser\Rss\ItunesRssParser::class, '6.0');
JLoader::registerAlias('JFeedParserRssMedia', \Joomla\CMS\Feed\Parser\Rss\MediaRssParser::class, '6.0');

JLoader::registerAlias('JImage', \Joomla\CMS\Image\Image::class, '6.0');
JLoader::registerAlias('JImageFilter', \Joomla\CMS\Image\ImageFilter::class, '6.0');
JLoader::registerAlias('JImageFilterBackgroundfill', \Joomla\CMS\Image\Filter\Backgroundfill::class, '6.0');
JLoader::registerAlias('JImageFilterBrightness', \Joomla\CMS\Image\Filter\Brightness::class, '6.0');
JLoader::registerAlias('JImageFilterContrast', \Joomla\CMS\Image\Filter\Contrast::class, '6.0');
JLoader::registerAlias('JImageFilterEdgedetect', \Joomla\CMS\Image\Filter\Edgedetect::class, '6.0');
JLoader::registerAlias('JImageFilterEmboss', \Joomla\CMS\Image\Filter\Emboss::class, '6.0');
JLoader::registerAlias('JImageFilterNegate', \Joomla\CMS\Image\Filter\Negate::class, '6.0');
JLoader::registerAlias('JImageFilterSmooth', \Joomla\CMS\Image\Filter\Smooth::class, '6.0');

JLoader::registerAlias('JObject', \Joomla\CMS\Object\CMSObject::class, '6.0');

JLoader::registerAlias('JExtensionHelper', \Joomla\CMS\Extension\ExtensionHelper::class, '6.0');

JLoader::registerAlias('JHtml', \Joomla\CMS\HTML\HTMLHelper::class, '6.0');

JLoader::registerAlias('\\Joomla\\Application\\Cli\\CliInput', \Joomla\CMS\Application\CLI\CliInput::class, '6.0');
JLoader::registerAlias('\\Joomla\\Application\\Cli\\CliOutput', \Joomla\CMS\Application\CLI\CliOutput::class, '6.0');
JLoader::registerAlias('\\Joomla\\Application\\Cli\\ColorStyle', \Joomla\CMS\Application\CLI\ColorStyle::class, '6.0');
JLoader::registerAlias('\\Joomla\\Application\\Cli\\Output\\Stdout', \Joomla\CMS\Application\CLI\Output\Stdout::class, '6.0');
JLoader::registerAlias('\\Joomla\\Application\\Cli\\Output\\Xml', \Joomla\CMS\Application\CLI\Output\Xml::class, '6.0');
JLoader::registerAlias(
    '\\Joomla\\Application\\Cli\\Output\\Processor\\ColorProcessor',
    \Joomla\CMS\Application\CLI\Output\Processor\ColorProcessor::class,
    '6.0'
);
JLoader::registerAlias(
    '\\Joomla\\Application\\Cli\\Output\\Processor\\ProcessorInterface',
    \Joomla\CMS\Application\CLI\Output\Processor\ProcessorInterface::class,
    '6.0'
);

JLoader::registerAlias('JFile', \Joomla\CMS\Filesystem\File::class, '6.0');
JLoader::registerAlias('JFolder', \Joomla\CMS\Filesystem\Folder::class, '6.0');
JLoader::registerAlias('JFilesystemHelper', \Joomla\CMS\Filesystem\FilesystemHelper::class, '6.0');
JLoader::registerAlias('JFilesystemPatcher', \Joomla\CMS\Filesystem\Patcher::class, '6.0');
JLoader::registerAlias('JPath', \Joomla\CMS\Filesystem\Path::class, '6.0');
JLoader::registerAlias('JStream', \Joomla\CMS\Filesystem\Stream::class, '6.0');
JLoader::registerAlias('JStreamString', \Joomla\CMS\Filesystem\Streams\StreamString::class, '6.0');
JLoader::registerAlias('JStringController', \Joomla\CMS\Filesystem\Support\StringController::class, '6.0');

JLoader::registerAlias('JClassLoader', \Joomla\CMS\Autoload\ClassLoader::class, '6.0');

JLoader::registerAlias('JFormFilterInt_Array', \Joomla\CMS\Form\Filter\IntarrayFilter::class, '6.0');

JLoader::registerAlias('JAdapter', \Joomla\CMS\Adapter\Adapter::class, '6.0');
JLoader::registerAlias('JAdapterInstance', \Joomla\CMS\Adapter\AdapterInstance::class, '6.0');

JLoader::registerAlias('JHtmlAccess', \Joomla\CMS\HTML\Helpers\Access::class, '6.0');
JLoader::registerAlias('JHtmlActionsDropdown', \Joomla\CMS\HTML\Helpers\ActionsDropdown::class, '6.0');
JLoader::registerAlias('JHtmlAdminLanguage', \Joomla\CMS\HTML\Helpers\AdminLanguage::class, '6.0');
JLoader::registerAlias('JHtmlBehavior', \Joomla\CMS\HTML\Helpers\Behavior::class, '6.0');
JLoader::registerAlias('JHtmlBootstrap', \Joomla\CMS\HTML\Helpers\Bootstrap::class, '6.0');
JLoader::registerAlias('JHtmlCategory', \Joomla\CMS\HTML\Helpers\Category::class, '6.0');
JLoader::registerAlias('JHtmlContent', \Joomla\CMS\HTML\Helpers\Content::class, '6.0');
JLoader::registerAlias('JHtmlContentlanguage', \Joomla\CMS\HTML\Helpers\ContentLanguage::class, '6.0');
JLoader::registerAlias('JHtmlDate', \Joomla\CMS\HTML\Helpers\Date::class, '6.0');
JLoader::registerAlias('JHtmlDebug', \Joomla\CMS\HTML\Helpers\Debug::class, '6.0');
JLoader::registerAlias('JHtmlDraggablelist', \Joomla\CMS\HTML\Helpers\DraggableList::class, '6.0');
JLoader::registerAlias('JHtmlDropdown', \Joomla\CMS\HTML\Helpers\Dropdown::class, '6.0');
JLoader::registerAlias('JHtmlEmail', \Joomla\CMS\HTML\Helpers\Email::class, '6.0');
JLoader::registerAlias('JHtmlForm', \Joomla\CMS\HTML\Helpers\Form::class, '6.0');
JLoader::registerAlias('JHtmlFormbehavior', \Joomla\CMS\HTML\Helpers\FormBehavior::class, '6.0');
JLoader::registerAlias('JHtmlGrid', \Joomla\CMS\HTML\Helpers\Grid::class, '6.0');
JLoader::registerAlias('JHtmlIcons', \Joomla\CMS\HTML\Helpers\Icons::class, '6.0');
JLoader::registerAlias('JHtmlJGrid', \Joomla\CMS\HTML\Helpers\JGrid::class, '6.0');
JLoader::registerAlias('JHtmlJquery', \Joomla\CMS\HTML\Helpers\Jquery::class, '6.0');
JLoader::registerAlias('JHtmlLinks', \Joomla\CMS\HTML\Helpers\Links::class, '6.0');
JLoader::registerAlias('JHtmlList', \Joomla\CMS\HTML\Helpers\ListHelper::class, '6.0');
JLoader::registerAlias('JHtmlMenu', \Joomla\CMS\HTML\Helpers\Menu::class, '6.0');
JLoader::registerAlias('JHtmlNumber', \Joomla\CMS\HTML\Helpers\Number::class, '6.0');
JLoader::registerAlias('JHtmlSearchtools', \Joomla\CMS\HTML\Helpers\SearchTools::class, '6.0');
JLoader::registerAlias('JHtmlSelect', \Joomla\CMS\HTML\Helpers\Select::class, '6.0');
JLoader::registerAlias('JHtmlSidebar', \Joomla\CMS\HTML\Helpers\Sidebar::class, '6.0');
JLoader::registerAlias('JHtmlSortableList', \Joomla\CMS\HTML\Helpers\SortableList::class, '6.0');
JLoader::registerAlias('JHtmlString', \Joomla\CMS\HTML\Helpers\StringHelper::class, '6.0');
JLoader::registerAlias('JHtmlTag', \Joomla\CMS\HTML\Helpers\Tag::class, '6.0');
JLoader::registerAlias('JHtmlTel', \Joomla\CMS\HTML\Helpers\Telephone::class, '6.0');
JLoader::registerAlias('JHtmlUser', \Joomla\CMS\HTML\Helpers\User::class, '6.0');
