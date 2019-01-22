<?php
/**
 * @package        HEAD. Article Module
 * @version        1.8.8
 * 
 * @author         Carsten Ruppert <webmaster@headmarketing.de>
 * @link           https://www.headmarketing.de
 * @copyright      Copyright © 2018 HEAD. MARKETING GmbH All Rights Reserved
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright    Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;


//
// Ausgabe des Weiterlesen-Link von einem Beitrag im Modul
//
// $item_readmore_url wird im Template _item.php deklariert: $item_readmore_url = ModArticlesHeadHelper::getReadmoreUrl($item);
//
    if($item_readmore_url !== '') :
?>
        <div class="readmore">
            <a itemprop="url" class="btn btn-primary more" href="<?php echo $item_readmore_url; ?>"<?php echo $item_readmore_blank ? ' target="_blank"' : '';?>>
                <span>
                    <?php 
                        if ($readmore = $item->alternative_readmore) :
                            echo $readmore;
                            if ($attribs->get('show_readmore_title', 0) != 0) :
                                echo \Joomla\CMS\HTML\HTMLHelper::_('string.truncate', $item->title, $attribs->get('readmore_limit'));
                            endif;
                        elseif ($attribs->get('show_readmore_title', 0) == 0) :
                            echo \Joomla\CMS\Language\Text::sprintf('COM_CONTENT_READ_MORE_TITLE');
                        else :
                            echo \Joomla\CMS\Language\Text::_('COM_CONTENT_READ_MORE');
                            echo \Joomla\CMS\HTML\HTMLHelper::_('string.truncate', $item->title, $attribs->get('readmore_limit'));
                        endif;
                    ?>				
                </span>
                <i></i>
            </a>
        </div>
<?php
    endif;	
?>