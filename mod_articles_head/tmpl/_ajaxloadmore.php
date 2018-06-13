<?php
/**
 * @package        HEAD. Article Module
 * @version        1.7.4
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

// -- AJAX-Request Konfiguration vom Helper holen
$config = ModArticlesHeadHelper::getAjaxLinkConfig($module);

// -- Ausgabe nur, wenn Start ($config->s) größer als Gesamtanzahl der Beiträge ist:
if( $config->s < $fullItemsCount ):
?>
	<div class="mod-intro-loadmore">
		<a  tabindex="0" 
            class="btn btn-primary" 
            data-modintroajax='<?php echo json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);?>'
        >
			<span><?php echo $params->get('ajax_loadmore_label','') != '' ? $params->get('ajax_loadmore_label','') : JText::_("MOD_ARTICLES_HEAD_AJAXLOADMORE_LABEL");?></span> <i class="fas fa-plus"></i>
		</a>
	</div>
<?php
endif;
?>