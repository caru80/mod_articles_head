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


    if($item_readmore_url !== '') :
?>
        <div class="readmore">
            <a itemprop="url" class="btn btn-primary more" href="<?php echo $item_readmore_url; ?>"<?php echo $item_readmore_blank ? ' target="_blank"' : '';?>>
                <span>
                    <?php 
                        if ($readmore = $item->alternative_readmore) :
                            echo $readmore;
                            if ($attribs->get('show_readmore_title', 0) != 0) :
                                echo JHtml::_('string.truncate', $item->title, $attribs->get('readmore_limit'));
                            endif;
                        elseif ($attribs->get('show_readmore_title', 0) == 0) :
                            echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
                        else :
                            echo JText::_('COM_CONTENT_READ_MORE');
                            echo JHtml::_('string.truncate', $item->title, $attribs->get('readmore_limit'));
                        endif;
                    ?>				
                </span>
                <i></i>
            </a>
        </div>
<?php
    endif;	
?>