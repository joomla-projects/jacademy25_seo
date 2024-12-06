<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_scheduler
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Scheduler\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Logs list controller class.
 *
 * @since  __DEPLOY_VERSION__
 */
class LogsController extends AdminController
{
    /**
     * The prefix to use with controller messages.
     *
     * @var    string
     *
     * @since   __DEPLOY_VERSION__
     */
    protected $text_prefix = 'COM_SCHEDULER_LOGS';

    /**
     * Proxy for getModel.
     *
     * @param   string  $name    The name of the model.
     * @param   string  $prefix  The prefix for the PHP class name.
     * @param   array   $config  Array of configuration parameters.
     *
     * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel
     *
     * @since   __DEPLOY_VERSION__
     */
    public function getModel($name = 'Log', $prefix = 'Administrator', $config = ['ignore_request' => true])
    {
        return parent::getModel($name, $prefix, $config);
    }

    /**
     * Clean out the logs.
     *
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function purge()
    {
        // Check for request forgeries.
        $this->checkToken();

        $model = $this->getModel('Logs');

        if ($model->purge()) {
            $message = Text::_('COM_SCHEDULER_LOGS_CLEAR');
        } else {
            $message = Text::_('COM_SCHEDULER_CLEAR_FAIL');
        }

        $this->setRedirect('index.php?option=com_scheduler&view=logs', $message);
    }
}
