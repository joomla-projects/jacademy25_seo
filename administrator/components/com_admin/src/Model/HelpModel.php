<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Admin\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\Help\Help;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\Filesystem\Folder;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Admin Component Help Model
 *
 * @since  1.6
 */
class HelpModel extends BaseDatabaseModel
{
    /**
     * The search string
     *
     * @var    string
     * @since  1.6
     */
    protected $help_search = null;

    /**
     * The page to be viewed
     *
     * @var    string
     * @since  1.6
     */
    protected $page = null;

    /**
     * The ISO language tag
     *
     * @var    string
     * @since  1.6
     */
    protected $lang_tag = null;

    /**
     * Table of contents
     *
     * @var    array
     * @since  1.6
     */
    protected $toc = [];

    /**
     * URL for the latest version check
     *
     * @var    string
     * @since  1.6
     */
    protected $latest_version_check = null;

    /**
     * Method to get the help search string
     *
     * @return  string  Help search string
     *
     * @since   1.6
     */
    public function &getHelpSearch()
    {
        if (\is_null($this->help_search)) {
            $this->help_search = Factory::getApplication()->getInput()->getString('helpsearch');
        }

        return $this->help_search;
    }

    /**
     * Method to get the page
     *
     * @return  string  The page
     *
     * @since   1.6
     */
    public function &getPage()
    {
        if (\is_null($this->page)) {
            $this->page = Help::createUrl(Factory::getApplication()->getInput()->get('page', 'Start_Here'));
        }

        return $this->page;
    }

    /**
     * Method to get the lang tag
     *
     * @return  string  lang iso tag
     *
     * @since  1.6
     */
    public function getLangTag()
    {
        if (\is_null($this->lang_tag)) {
            $this->lang_tag = Factory::getLanguage()->getTag();

            if (!is_dir(JPATH_BASE . '/help/' . $this->lang_tag)) {
                // Use English as fallback
                $this->lang_tag = 'en-GB';
            }
        }

        return $this->lang_tag;
    }

    /**
     * Method to get the table of contents
     *
     * @return  array  Table of contents
     */
    public function &getToc()
    {
        if (\count($this->toc)) {
            return $this->toc;
        }

        // Get vars
        $lang_tag    = $this->getLangTag();
        $help_search = $this->getHelpSearch();

        // New style - Check for a TOC \JSON file
        if (!file_exists(JPATH_BASE . '/help/' . $lang_tag . '/toc.json')) {
            // Load the language file as a text file.
            $items = file_get_contents(JPATH_BASE . '/language/en-GB/com_admin.ini');

            $lines = preg_split("/((\r?\n)|(\r\n?))/", $items);
            $pattern = '/COM_ADMIN_HELP_(.*?)="(.*)"/';
            $data = [];
            foreach ($lines as $line) {
                if (strpos($line, 'COM_ADMIN_HELP_') !== 0) {
                    continue;
                }
                preg_match($pattern, $line, $matches);
                // $matches[1] is the key, $matches[2] is the value
                $key = str_replace('_', ' ', $matches[1]);
                $key = str_replace(' ', '_', ucwords(strtolower($key)));
                // List of words that should not be capitalised:
                // of, or, for, is
                // list of words that should be capitalised
                // URL, CMS
                $key = str_replace('_Who_Is_', '_Who\s_', $key);
                $key = str_replace('_For_', '_for_', $key);
                $key = str_replace('_Is_', '_is_', $key);
                $key = str_replace('_Of_', '_of_', $key);
                $key = str_replace('_Or_', '_or_', $key);
                $key = str_replace('_Url', '_URL', $key);
                $key = str_replace('_Cms', '_CMS', $key);

                $data[$key] = $matches[1];
            }

            $json = json_encode($data);
            file_put_contents(JPATH_BASE . '/help/' . $lang_tag . '/toc.json', $json);
        } else {
            $data = json_decode(file_get_contents(JPATH_BASE . '/help/' . $lang_tag . '/toc.json'));
        }

        // Loop through the data array
        foreach ($data as $key => $value) {
            $this->toc[$key] = Text::_('COM_ADMIN_HELP_' . $value);
        }

        // Sort the Table of Contents for the selected language
        asort($this->toc);

        return $this->toc;
    }
}
