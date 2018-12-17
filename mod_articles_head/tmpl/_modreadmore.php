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


// -- Modul-Weiterlesen URL
$moduleReadmore = false;
if( $params->get('linkeditem','') != '' ) // -- Zu einem Menüeintrag
{
	$menuitem = \Joomla\CMS\Factory::getApplication()->getMenu()->getItem($params->get('linkeditem',''));
	$moduleReadmore = \Joomla\CMS\Router\Route::_($menuitem->link.'&Itemid='.$menuitem->id);

}
elseif( $params->get('linkcategory',0) != 0 ) // -- Zur ersten gewählten Kategorie
{
	$categories = $params->get('catid', false); // wg. Notice: Strict Standards...
	$moduleReadmore = \Joomla\CMS\Router\Route::_(ContentHelperRoute::getCategoryRoute(reset($categories)));
}

?>
<?php
    if($moduleReadmore):
?>
        <div class="mod-intro-readmore">
            <a href="<?php echo $moduleReadmore;?>" class="btn btn-primary more">
                <span><?php echo $params->get('module_readmore_label','') != '' ? $params->get('module_readmore_label','') : JText::_("MOD_ARTICLES_HEAD_MODULEREADMORE_LABEL_FRONT");?></span> <i class="fas fa-chevron-right"></i>
            </a>
        </div>
<?php
    endif;
?>