<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Event;

use Joomla\CMS\Event\ActionLog\AfterLogExportEvent;
use Joomla\CMS\Event\ActionLog\AfterLogPurgeEvent;
use Joomla\CMS\Event\Application\AfterApiRouteEvent;
use Joomla\CMS\Event\Application\AfterCompressEvent;
use Joomla\CMS\Event\Application\AfterDispatchEvent;
use Joomla\CMS\Event\Application\AfterExecuteEvent;
use Joomla\CMS\Event\Application\AfterInitialiseEvent;
use Joomla\CMS\Event\Application\AfterRenderEvent;
use Joomla\CMS\Event\Application\AfterRespondEvent;
use Joomla\CMS\Event\Application\AfterRouteEvent;
use Joomla\CMS\Event\Application\AfterSaveConfigurationEvent;
use Joomla\CMS\Event\Application\BeforeApiRouteEvent;
use Joomla\CMS\Event\Application\BeforeCompileHeadEvent;
use Joomla\CMS\Event\Application\BeforeExecuteEvent;
use Joomla\CMS\Event\Application\BeforeRenderEvent;
use Joomla\CMS\Event\Application\BeforeRespondEvent;
use Joomla\CMS\Event\Application\BeforeSaveConfigurationEvent;
use Joomla\CMS\Event\Cache\AfterPurgeEvent;
use Joomla\CMS\Event\Contact\SubmitContactEvent;
use Joomla\CMS\Event\Contact\ValidateContactEvent;
use Joomla\CMS\Event\Content\AfterDisplayEvent;
use Joomla\CMS\Event\Content\AfterTitleEvent;
use Joomla\CMS\Event\Content\BeforeDisplayEvent;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Event\CustomFields\AfterPrepareFieldEvent;
use Joomla\CMS\Event\CustomFields\BeforePrepareFieldEvent;
use Joomla\CMS\Event\CustomFields\GetTypesEvent;
use Joomla\CMS\Event\CustomFields\PrepareDomEvent;
use Joomla\CMS\Event\CustomFields\PrepareFieldEvent;
use Joomla\CMS\Event\Extension\AfterInstallEvent;
use Joomla\CMS\Event\Extension\AfterJoomlaUpdateEvent;
use Joomla\CMS\Event\Extension\AfterUninstallEvent;
use Joomla\CMS\Event\Extension\AfterUpdateEvent;
use Joomla\CMS\Event\Extension\BeforeInstallEvent;
use Joomla\CMS\Event\Extension\BeforeJoomlaUpdateEvent;
use Joomla\CMS\Event\Extension\BeforeUninstallEvent;
use Joomla\CMS\Event\Extension\BeforeUpdateEvent;
use Joomla\CMS\Event\Finder\BeforeIndexEvent;
use Joomla\CMS\Event\Finder\BuildIndexEvent;
use Joomla\CMS\Event\Finder\GarbageCollectionEvent;
use Joomla\CMS\Event\Finder\PrepareContentEvent;
use Joomla\CMS\Event\Finder\ResultEvent;
use Joomla\CMS\Event\Finder\StartIndexEvent;
use Joomla\CMS\Event\Installer\AddInstallationTabEvent;
use Joomla\CMS\Event\Installer\AfterInstallerEvent;
use Joomla\CMS\Event\Installer\BeforeInstallationEvent;
use Joomla\CMS\Event\Installer\BeforeInstallerEvent;
use Joomla\CMS\Event\Installer\BeforePackageDownloadEvent;
use Joomla\CMS\Event\Mail\BeforeRenderingMailTemplateEvent;
use Joomla\CMS\Event\Menu\AfterGetMenuTypeOptionsEvent;
use Joomla\CMS\Event\Menu\BeforeRenderMenuItemsViewEvent;
use Joomla\CMS\Event\Menu\PreprocessMenuItemsEvent;
use Joomla\CMS\Event\Model\AfterCategoryChangeStateEvent;
use Joomla\CMS\Event\Model\AfterChangeStateEvent;
use Joomla\CMS\Event\Model\AfterSaveEvent;
use Joomla\CMS\Event\Model\BeforeBatchEvent;
use Joomla\CMS\Event\Model\BeforeChangeStateEvent;
use Joomla\CMS\Event\Model\BeforeSaveEvent;
use Joomla\CMS\Event\Model\BeforeValidateDataEvent;
use Joomla\CMS\Event\Model\NormaliseRequestDataEvent;
use Joomla\CMS\Event\Module\AfterCleanModuleListEvent;
use Joomla\CMS\Event\Module\AfterModuleListEvent;
use Joomla\CMS\Event\Module\AfterRenderModuleEvent;
use Joomla\CMS\Event\Module\AfterRenderModulesEvent;
use Joomla\CMS\Event\Module\BeforeRenderModuleEvent;
use Joomla\CMS\Event\Module\PrepareModuleListEvent;
use Joomla\CMS\Event\PageCache\GetKeyEvent;
use Joomla\CMS\Event\PageCache\IsExcludedEvent;
use Joomla\CMS\Event\PageCache\SetCachingEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareDataEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareFormEvent;
use Joomla\CMS\Event\Plugin\System\Schemaorg\PrepareSaveEvent;
use Joomla\CMS\Event\Privacy\CanRemoveDataEvent;
use Joomla\CMS\Event\Privacy\CheckPrivacyPolicyPublishedEvent;
use Joomla\CMS\Event\Privacy\CollectCapabilitiesEvent;
use Joomla\CMS\Event\Privacy\ExportRequestEvent;
use Joomla\CMS\Event\Privacy\RemoveDataEvent;
use Joomla\CMS\Event\QuickIcon\GetIconEvent;
use Joomla\CMS\Event\Table\AfterBindEvent;
use Joomla\CMS\Event\Table\AfterCheckinEvent;
use Joomla\CMS\Event\Table\AfterCheckoutEvent;
use Joomla\CMS\Event\Table\AfterDeleteEvent;
use Joomla\CMS\Event\Table\AfterHitEvent;
use Joomla\CMS\Event\Table\AfterLoadEvent;
use Joomla\CMS\Event\Table\AfterMoveEvent;
use Joomla\CMS\Event\Table\AfterPublishEvent;
use Joomla\CMS\Event\Table\AfterReorderEvent;
use Joomla\CMS\Event\Table\AfterResetEvent;
use Joomla\CMS\Event\Table\AfterStoreEvent;
use Joomla\CMS\Event\Table\BeforeBindEvent;
use Joomla\CMS\Event\Table\BeforeCheckinEvent;
use Joomla\CMS\Event\Table\BeforeCheckoutEvent;
use Joomla\CMS\Event\Table\BeforeDeleteEvent;
use Joomla\CMS\Event\Table\BeforeHitEvent;
use Joomla\CMS\Event\Table\BeforeLoadEvent;
use Joomla\CMS\Event\Table\BeforeMoveEvent;
use Joomla\CMS\Event\Table\BeforePublishEvent;
use Joomla\CMS\Event\Table\BeforeReorderEvent;
use Joomla\CMS\Event\Table\BeforeResetEvent;
use Joomla\CMS\Event\Table\BeforeStoreEvent;
use Joomla\CMS\Event\Table\CheckEvent;
use Joomla\CMS\Event\Table\ObjectCreateEvent;
use Joomla\CMS\Event\Table\SetNewTagsEvent;
use Joomla\CMS\Event\User\AfterLoginEvent;
use Joomla\CMS\Event\User\AfterLogoutEvent;
use Joomla\CMS\Event\User\AfterRemindEvent;
use Joomla\CMS\Event\User\AfterResetCompleteEvent;
use Joomla\CMS\Event\User\AfterResetRequestEvent;
use Joomla\CMS\Event\User\AuthenticationEvent;
use Joomla\CMS\Event\User\AuthorisationEvent;
use Joomla\CMS\Event\User\AuthorisationFailureEvent;
use Joomla\CMS\Event\User\BeforeResetCompleteEvent;
use Joomla\CMS\Event\User\BeforeResetRequestEvent;
use Joomla\CMS\Event\User\LoginButtonsEvent;
use Joomla\CMS\Event\User\LoginEvent;
use Joomla\CMS\Event\User\LoginFailureEvent;
use Joomla\CMS\Event\User\LogoutEvent;
use Joomla\CMS\Event\User\LogoutFailureEvent;
use Joomla\CMS\Event\View\DisplayEvent;
use Joomla\CMS\Event\Workflow\WorkflowFunctionalityUsedEvent;
use Joomla\CMS\Event\Workflow\WorkflowTransitionEvent;
use Joomla\Event\Event;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Returns the most suitable event class for a Joomla core event name
 *
 * @since 4.2.0
 */
trait CoreEventAware
{
    /**
     * Maps event names to concrete Event classes.
     *
     * This is only for events with invariable names. Events with variable names are handled with
     * PHP logic in the getEventClassByEventName class.
     *
     * @var   array
     * @since 4.2.0
     */
    private static $eventNameToConcreteClass = [
        // Application
        'onBeforeExecute'     => BeforeExecuteEvent::class,
        'onAfterExecute'      => AfterExecuteEvent::class,
        'onAfterInitialise'   => AfterInitialiseEvent::class,
        'onAfterRoute'        => AfterRouteEvent::class,
        'onBeforeApiRoute'    => BeforeApiRouteEvent::class,
        'onAfterApiRoute'     => AfterApiRouteEvent::class,
        'onAfterDispatch'     => AfterDispatchEvent::class,
        'onBeforeRender'      => BeforeRenderEvent::class,
        'onAfterRender'       => AfterRenderEvent::class,
        'onBeforeCompileHead' => BeforeCompileHeadEvent::class,
        'onAfterCompress'     => AfterCompressEvent::class,
        'onBeforeRespond'     => BeforeRespondEvent::class,
        'onAfterRespond'      => AfterRespondEvent::class,
        'onError'             => ErrorEvent::class,
        // Application configuration
        'onApplicationBeforeSave' => BeforeSaveConfigurationEvent::class,
        'onApplicationAfterSave'  => AfterSaveConfigurationEvent::class,
        // Quickicon
        'onGetIcon' => GetIconEvent::class,
        // Table
        'onTableAfterBind'      => AfterBindEvent::class,
        'onTableAfterCheckin'   => AfterCheckinEvent::class,
        'onTableAfterCheckout'  => AfterCheckoutEvent::class,
        'onTableAfterDelete'    => AfterDeleteEvent::class,
        'onTableAfterHit'       => AfterHitEvent::class,
        'onTableAfterLoad'      => AfterLoadEvent::class,
        'onTableAfterMove'      => AfterMoveEvent::class,
        'onTableAfterPublish'   => AfterPublishEvent::class,
        'onTableAfterReorder'   => AfterReorderEvent::class,
        'onTableAfterReset'     => AfterResetEvent::class,
        'onTableAfterStore'     => AfterStoreEvent::class,
        'onTableBeforeBind'     => BeforeBindEvent::class,
        'onTableBeforeCheckin'  => BeforeCheckinEvent::class,
        'onTableBeforeCheckout' => BeforeCheckoutEvent::class,
        'onTableBeforeDelete'   => BeforeDeleteEvent::class,
        'onTableBeforeHit'      => BeforeHitEvent::class,
        'onTableBeforeLoad'     => BeforeLoadEvent::class,
        'onTableBeforeMove'     => BeforeMoveEvent::class,
        'onTableBeforePublish'  => BeforePublishEvent::class,
        'onTableBeforeReorder'  => BeforeReorderEvent::class,
        'onTableBeforeReset'    => BeforeResetEvent::class,
        'onTableBeforeStore'    => BeforeStoreEvent::class,
        'onTableCheck'          => CheckEvent::class,
        'onTableObjectCreate'   => ObjectCreateEvent::class,
        'onTableSetNewTags'     => SetNewTagsEvent::class,
        // View
        'onBeforeDisplay' => DisplayEvent::class,
        'onAfterDisplay'  => DisplayEvent::class,
        // Workflow
        'onWorkflowFunctionalityUsed' => WorkflowFunctionalityUsedEvent::class,
        'onWorkflowAfterTransition'   => WorkflowTransitionEvent::class,
        'onWorkflowBeforeTransition'  => WorkflowTransitionEvent::class,
        // Plugin: System, Schemaorg
        'onSchemaBeforeCompileHead' => Plugin\System\Schemaorg\BeforeCompileHeadEvent::class,
        'onSchemaPrepareData'       => PrepareDataEvent::class,
        'onSchemaPrepareForm'       => PrepareFormEvent::class,
        'onSchemaPrepareSave'       => PrepareSaveEvent::class,
        // Content
        'onContentPrepare'       => ContentPrepareEvent::class,
        'onContentAfterTitle'    => AfterTitleEvent::class,
        'onContentBeforeDisplay' => BeforeDisplayEvent::class,
        'onContentAfterDisplay'  => AfterDisplayEvent::class,
        // Model
        'onContentNormaliseRequestData' => NormaliseRequestDataEvent::class,
        'onContentBeforeValidateData'   => BeforeValidateDataEvent::class,
        'onContentPrepareForm'          => Model\PrepareFormEvent::class,
        'onContentPrepareData'          => Model\PrepareDataEvent::class,
        'onContentBeforeSave'           => BeforeSaveEvent::class,
        'onContentAfterSave'            => AfterSaveEvent::class,
        'onContentBeforeDelete'         => Model\BeforeDeleteEvent::class,
        'onContentAfterDelete'          => Model\AfterDeleteEvent::class,
        'onContentBeforeChangeState'    => BeforeChangeStateEvent::class,
        'onContentChangeState'          => AfterChangeStateEvent::class,
        'onCategoryChangeState'         => AfterCategoryChangeStateEvent::class,
        'onBeforeBatch'                 => BeforeBatchEvent::class,
        // User
        'onUserAuthenticate'         => AuthenticationEvent::class,
        'onUserAuthorisation'        => AuthorisationEvent::class,
        'onUserAuthorisationFailure' => AuthorisationFailureEvent::class,
        'onUserLogin'                => LoginEvent::class,
        'onUserAfterLogin'           => AfterLoginEvent::class,
        'onUserLoginFailure'         => LoginFailureEvent::class,
        'onUserLogout'               => LogoutEvent::class,
        'onUserAfterLogout'          => AfterLogoutEvent::class,
        'onUserLogoutFailure'        => LogoutFailureEvent::class,
        'onUserLoginButtons'         => LoginButtonsEvent::class,
        'onUserBeforeSave'           => User\BeforeSaveEvent::class,
        'onUserAfterSave'            => User\AfterSaveEvent::class,
        'onUserBeforeDelete'         => User\BeforeDeleteEvent::class,
        'onUserAfterDelete'          => User\AfterDeleteEvent::class,
        'onUserAfterRemind'          => AfterRemindEvent::class,
        'onUserBeforeResetRequest'   => BeforeResetRequestEvent::class,
        'onUserAfterResetRequest'    => AfterResetRequestEvent::class,
        'onUserBeforeResetComplete'  => BeforeResetCompleteEvent::class,
        'onUserAfterResetComplete'   => AfterResetCompleteEvent::class,
        // User Group
        'onUserBeforeSaveGroup'   => BeforeSaveEvent::class,
        'onUserAfterSaveGroup'    => AfterSaveEvent::class,
        'onUserBeforeDeleteGroup' => Model\BeforeDeleteEvent::class,
        'onUserAfterDeleteGroup'  => Model\AfterDeleteEvent::class,
        // Modules
        'onRenderModule'         => BeforeRenderModuleEvent::class,
        'onAfterRenderModule'    => AfterRenderModuleEvent::class,
        'onAfterRenderModules'   => AfterRenderModulesEvent::class,
        'onPrepareModuleList'    => PrepareModuleListEvent::class,
        'onAfterModuleList'      => AfterModuleListEvent::class,
        'onAfterCleanModuleList' => AfterCleanModuleListEvent::class,
        // Extension
        'onBeforeExtensionBoot'      => BeforeExtensionBootEvent::class,
        'onAfterExtensionBoot'       => AfterExtensionBootEvent::class,
        'onExtensionBeforeInstall'   => BeforeInstallEvent::class,
        'onExtensionAfterInstall'    => AfterInstallEvent::class,
        'onExtensionBeforeUninstall' => BeforeUninstallEvent::class,
        'onExtensionAfterUninstall'  => AfterUninstallEvent::class,
        'onExtensionBeforeUpdate'    => BeforeUpdateEvent::class,
        'onExtensionAfterUpdate'     => AfterUpdateEvent::class,
        'onExtensionBeforeSave'      => BeforeSaveEvent::class,
        'onExtensionAfterSave'       => AfterSaveEvent::class,
        'onExtensionAfterDelete'     => Model\AfterDeleteEvent::class,
        'onExtensionChangeState'     => BeforeChangeStateEvent::class,
        'onJoomlaBeforeUpdate'       => BeforeJoomlaUpdateEvent::class,
        'onJoomlaAfterUpdate'        => AfterJoomlaUpdateEvent::class,
        // Installer
        'onInstallerAddInstallationTab'    => AddInstallationTabEvent::class,
        'onInstallerBeforeInstallation'    => BeforeInstallationEvent::class,
        'onInstallerBeforeInstaller'       => BeforeInstallerEvent::class,
        'onInstallerAfterInstaller'        => AfterInstallerEvent::class,
        'onInstallerBeforePackageDownload' => BeforePackageDownloadEvent::class,
        // Finder
        'onFinderCategoryChangeState' => Finder\AfterCategoryChangeStateEvent::class,
        'onFinderChangeState'         => Finder\AfterChangeStateEvent::class,
        'onFinderAfterDelete'         => Finder\AfterDeleteEvent::class,
        'onFinderBeforeSave'          => Finder\BeforeSaveEvent::class,
        'onFinderAfterSave'           => Finder\AfterSaveEvent::class,
        'onFinderResult'              => ResultEvent::class,
        'onPrepareFinderContent'      => PrepareContentEvent::class,
        'onBeforeIndex'               => BeforeIndexEvent::class,
        'onBuildIndex'                => BuildIndexEvent::class,
        'onStartIndex'                => StartIndexEvent::class,
        'onFinderGarbageCollection'   => GarbageCollectionEvent::class,
        // Menu
        'onBeforeRenderMenuItems'   => BeforeRenderMenuItemsViewEvent::class,
        'onAfterGetMenuTypeOptions' => AfterGetMenuTypeOptionsEvent::class,
        'onPreprocessMenuItems'     => PreprocessMenuItemsEvent::class,
        // ActionLog
        'onAfterLogPurge'  => AfterLogPurgeEvent::class,
        'onAfterLogExport' => AfterLogExportEvent::class,
        // Cache
        'onAfterPurge' => AfterPurgeEvent::class,
        // Contact
        'onValidateContact' => ValidateContactEvent::class,
        'onSubmitContact'   => SubmitContactEvent::class,
        // Checkin
        'onAfterCheckin' => Checkin\AfterCheckinEvent::class,
        // Custom Fields
        'onCustomFieldsGetTypes'           => GetTypesEvent::class,
        'onCustomFieldsPrepareDom'         => PrepareDomEvent::class,
        'onCustomFieldsBeforePrepareField' => BeforePrepareFieldEvent::class,
        'onCustomFieldsPrepareField'       => PrepareFieldEvent::class,
        'onCustomFieldsAfterPrepareField'  => AfterPrepareFieldEvent::class,
        // Privacy
        'onPrivacyCollectAdminCapabilities'    => CollectCapabilitiesEvent::class,
        'onPrivacyCheckPrivacyPolicyPublished' => CheckPrivacyPolicyPublishedEvent::class,
        'onPrivacyExportRequest'               => ExportRequestEvent::class,
        'onPrivacyCanRemoveData'               => CanRemoveDataEvent::class,
        'onPrivacyRemoveData'                  => RemoveDataEvent::class,
        // PageCache
        'onPageCacheSetCaching' => SetCachingEvent::class,
        'onPageCacheGetKey'     => GetKeyEvent::class,
        'onPageCacheIsExcluded' => IsExcludedEvent::class,
        // Mail
        'onMailBeforeRendering' => BeforeRenderingMailTemplateEvent::class,
    ];

    /**
     * Get the concrete event class name for the given event name.
     *
     * This method falls back to the generic Joomla\Event\Event class if the event name is unknown
     * to this trait.
     *
     * @param   string  $eventName  The event name
     *
     * @return  string The event class name
     * @since 4.2.0
     */
    protected static function getEventClassByEventName(string $eventName): string
    {
        return self::$eventNameToConcreteClass[$eventName] ?? Event::class;
    }
}
