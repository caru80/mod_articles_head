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

use \Joomla\CMS\Helper\TagsHelper;

$tagsHelper = new TagsHelper();
$tags 		= $tagsHelper->getItemTags('com_content.article', $item->id);

if (count($tags)) :
	
	if( $params->get('ajax_enable', 0) ) :
		foreach($tags as $tag) :
			$ajax_config 			= $Helper->getAjaxLinkConfig();
			$ajax_config->replace 	= true;
			$ajax_config->start 	= 0;
			$ajax_config->tag 		= array($tag->id);

			$tag->data_ajax = json_encode($ajax_config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
		endforeach;
	endif;

	// Template Ausgabe: 
?>
	<ul class="item-tag-list">
	<?php
		foreach ($tags as $i => $tag) :
	?>
			<li>
				<span class="item-tag badge badge-primary" <?php echo $params->get('ajax_enable', 0) && isset($tag->data_ajax) ? " data-modintroajax='" . $tag->data_ajax . "'" : '';?>>
					<?php echo $tag->title;?>
				</span>
			</li>
	<?php
		endforeach;
	?>
	</ul>
<?php
endif;
?>