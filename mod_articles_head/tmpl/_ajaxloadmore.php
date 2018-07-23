<?php
/**
 * @package        HEAD. Article Module
 * @version        1.8.0
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
?>

<?php
    // -- „Mehr laden”:
if($params->get('ajax_loadmore_type', 0) == 1):
	
	$config = ModArticlesHeadHelper::getAjaxLinkConfig($module); // -- AJAX-Request Konfiguration vom Helper holen

	if($config->s < ModArticlesHeadHelper::getItemsCount($params, true)):
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
endif;
?>

<?php
    // -- Paginierung:
if($params->get('ajax_loadmore_type', 0) == 2):

	$list = ModArticlesHeadHelper::getPaginationList($module);

?>
	<div class="pagination mod-intro-pagination">
		<ul class="pagination">
			
            <?php $link = $list["start"];?>
			<li class="start<?php echo $link->disabled ? ' disabled' : '';?>">
				<a <?php echo $link->config != '' ? ' ' . $link->config : '';?>>
					<i class="fas fa-angle-double-left"></i> <?php echo $link->text;?>
				</a>
			</li>

			<?php $link = $list["previous"];?>
		    <li class="previous<?php echo $link->disabled ? ' disabled' : '';?>">
				<a<?php echo $link->config != '' ? ' ' . $link->config : '';?>>
					<i class="fas fa-angle-left"></i> <?php echo $link->text;?>
				</a>
			</li>

			<?php
				foreach($list["pages"] as $key => $link) :
			?>
                    <li class="page-<?php echo $key;?><?php echo $link->disabled ? ' disabled' : '';?><?php echo $link->current ? ' active' : '';?>">
                        <a<?php echo $link->config != '' ? ' ' . $link->config : '';?>>
                            <?php echo $link->text;?>
                        </a>
                    </li>
			<?php
				endforeach;
			?>

			<?php $link = $list["next"];?>
			<li class="next<?php echo $link->disabled ? ' disabled' : '';?>">
				<a<?php echo $link->config != '' ? ' ' . $link->config : '';?>>
					<?php echo $link->text;?> <i class="fas fa-angle-right"></i>
				</a>
			</li>

			<?php $link = $list["end"];?>
			<li class="end<?php echo $link->disabled ? ' disabled' : '';?>">
				<a<?php echo $link->config != '' ? ' ' . $link->config : '';?>>
					<?php echo $link->text;?> <i class="fas fa-angle-double-right"></i>
				</a>
			</li>

		</ul>
	</div>
<?php
endif;
?>