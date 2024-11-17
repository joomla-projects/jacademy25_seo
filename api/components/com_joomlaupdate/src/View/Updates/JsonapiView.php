<?php

/**
 * @package     Joomla.API
 * @subpackage  com_joomlaupdate
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Joomlaupdate\Api\View\Updates;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\MVC\View\JsonApiView as BaseApiView;
use Joomla\CMS\Updater\Updater;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Joomlaupdate\Administrator\Model\UpdateModel;
use Tobscure\JsonApi\Resource;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * The healthcheck view
 *
 * @since  __DEPLOY_VERSION__
 */
class JsonapiView extends BaseApiView
{
    /**
     * Generates the health check output
     *
     * @return string  The rendered data
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getUpdate() {
        $params = ComponentHelper::getParams('com_joomlaupdate');

        $updater = Updater::getInstance();

        // Here be dragons, crazy stuff to overcome some Joomla! restrictions

        // For automated updates, we stay in the current major version, so make sure we don't look for "next"
        // @todo remove this restriction to also update to the next major version
        // We also check if we want only patch level updates
        switch ($params->get('autoupdate')) {
            case 'patch':
                $params->set('patchOnly', true);
            case 'minor':
            case 'major':
            default:
                $params->set('updatesource', 'default');
                break;
        }

        $updates = $updater->getAvailableUpdates(ExtensionHelper::getExtensionRecord('joomla', 'file')->extension_id, Updater::STABILITY_STABLE);

        $latestVersion = null;

        foreach ($updates as $update) {
            if (!$latestVersion || version_compare($update['version'], $latestVersion) > 0) {
                $latestVersion = $update['version'];
            }
        }

        /**
         * @var UpdateModel $model
         */
        $model = $this->getModel();

        $latestVersion = $model->getAvailableAutoUpdates();

        $element = (new Resource(['availableUpdate' => $latestVersion], $this->serializer))
            ->fields(['checkUpdate' => ['availableUpdate']]);

        $this->getDocument()->setData($element);
        $this->getDocument()->addLink('self', Uri::current());

        return $this->getDocument()->render();
    }
}
