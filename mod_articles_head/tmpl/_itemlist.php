<?php
/**
 * @package        HEAD. Article Module
 * @version        2.0
 * 
 * @author         Carsten Ruppert <webmaster@headmarketing.de>
 * @link           https://www.headmarketing.de
 * @copyright      Copyright © 2018 - 2019 HEAD. MARKETING GmbH All Rights Reserved
 * @license        http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

/**
 * @copyright    Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license      GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
?>
<div class="mod-intro-items async" id="intro-<?php echo $module->id;?>-items-<?php echo $Helper->getState('start', 0);?>">
    <div class="list-row <?php echo $params->get('classnames_rows', '');?>">
        <?php 
            if($list !== false) :
                foreach ($list as $item) : 
                    require ModuleHelper::getLayoutPath('mod_articles_head', '_item');
                endforeach;
            endif;
        ?>
    </div>
</div>
<?php
//
// AJAX: „Mehr laden” oder Paginierung
// Das muss in dieses Template, weil nur dieses Template bei einem AJAX-Aufruf gerendert wird, und die Paginierung ersetzt werden muss.
// 
if ($params->get('ajax_enable', 0)) :
    require ModuleHelper::getLayoutPath('mod_articles_head', '_ajaxloadmore');
endif;
?>