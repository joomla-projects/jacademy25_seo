<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();

/** @var \Joomla\Component\Content\Site\View\Article\HtmlView $this */
// Create shortcut
$urls = json_decode($this->item->urls);

// Create shortcuts to some parameters.
$params = $this->item->params;
if ($urls && (!empty($urls->urla) || !empty($urls->urlb) || !empty($urls->urlc))) :
    ?>
<div class="com-content-article__links content-links">
    <ul class="com-content-article__links content-list">
        <?php
            $urlarray = [
            [$urls->urla, $urls->urlatext, $urls->targeta, 'a'],
            [$urls->urlb, $urls->urlbtext, $urls->targetb, 'b'],
            [$urls->urlc, $urls->urlctext, $urls->targetc, 'c']
            ];
            foreach ($urlarray as $url) :
                $link = $url[0];
                $label = $url[1];
                $target = $url[2];
                $id = $url[3];

                if (! $link) :
                    continue;
                endif;

                // If no label is present, take the link
                $label = $label ?: $link;

                // If no target is present, use the default
                $target = $target ?: $params->get('target' . $id);
                ?>
            <li class="com-content-article__link content-links-<?php echo $id; ?>">
                <?php
                    // Compute the correct link

                switch ($target) {
                    case 1:
                        // Open in a new window
                        echo '<a href="' . htmlspecialchars($link, ENT_COMPAT, 'UTF-8') . '" target="_blank" rel="nofollow noopener noreferrer">' .
                            htmlspecialchars($label, ENT_COMPAT, 'UTF-8') . '</a>';
                        break;

                    case 2:
                        // Open in a popup window
                        $attribs = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=600,height=600';
                        echo "<a href=\"" . htmlspecialchars($link, ENT_COMPAT, 'UTF-8') . "\" onclick=\"window.open(this.href, 'targetWindow', '" . $attribs . "'); return false;\" rel=\"noopener noreferrer\">" .
                            htmlspecialchars($label, ENT_COMPAT, 'UTF-8') . '</a>';
                        break;
                    case 3:
                        $wa->useScript('joomla.dialog-autocreate');
                        $popupOptions = ['textHeader' => $label, 'className' => 'dialog-content-link', 'width' => '800px', 'height' => '500px', 'preferredParent' => 'body'];

                        echo '<a href="' . htmlspecialchars($link, ENT_COMPAT, 'UTF-8') . '" rel="noopener noreferrer"'
                            . ' data-joomla-dialog="' . htmlspecialchars(json_encode($popupOptions, JSON_UNESCAPED_SLASHES), ENT_COMPAT, 'UTF-8') . '">' .
                            htmlspecialchars($label, ENT_COMPAT, 'UTF-8') . ' </a>';

                        break;

                    default:
                        // Open in parent window
                        echo '<a href="' . htmlspecialchars($link, ENT_COMPAT, 'UTF-8') . '" rel="nofollow">' .
                            htmlspecialchars($label, ENT_COMPAT, 'UTF-8') . ' </a>';
                        break;
                }
                ?>
                </li>
            <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>
