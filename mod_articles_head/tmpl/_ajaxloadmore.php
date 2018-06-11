<?php
/**
 * @package        HEAD. Article Module
 * @version        1.7.2
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


/**
	mod_articles_head
	
	AJAX – mehr laden.
*/
defined('_JEXEC') or die;

// -- AJAX-Request Konfiguration
$config = (object) [
			'url' 		=> JUri::root() . 'index.php',
			'id'		=> $module->id,
			's' 		=> $params->get('start',0) + $params->get('count', 4),
			'target' 	=> '#mod-intro-items-list-' . $module->id 
		];
// -- Post-Animationen
if($params->get('ajax_post_animations',0)) {
	$config->animate 	= true;
	$config->aniclass 	= $params->get('ajax_post_animation_class','');
	$config->aniname 	= $params->get('ajax_post_animation_name','');
}
?>
<?php
if( $config->s < $fullItemsCount ):
?>
	<div class="mod-intro-loadmore">
		<a tabindex="0" class="btn btn-primary" data-modintroajax='<?php echo json_encode($config);?>'>
			<span><?php echo $params->get('ajax_loadmore_label','') != '' ? $params->get('ajax_loadmore_label','') : JText::_("MOD_ARTICLES_HEAD_AJAXLOADMORE_LABEL");?></span> <i class="fas fa-plus"></i>
		</a>
	</div>
<?php
endif;
?>