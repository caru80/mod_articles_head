<?php
/**
 * @package        HEAD. Article Module
 * @version        1.9.0
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
?>
<?php
use \Joomla\CMS\Language\Text; // JText

//
// Mehr laden:
//
if($params->get('ajax_loadmore_type', 0) == 1):
	
	$config = ModArticlesHeadHelper::getAjaxLinkConfig($module); // -- AJAX-Request Konfiguration vom Helper holen
	
	if($config->s < ModArticlesHeadHelper::getItemsCount($params, true)): // Nur anzeigen, wenn es noch etwas zum Nachladen gibt.
?>
		<div class="mod-intro-loadmore">
			<a  tabindex="0" 
				class="btn btn-primary" 
				data-modintroajax='<?php echo json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);?>'
			>
				<span><?php echo $params->get('ajax_loadmore_label','') != '' ? $params->get('ajax_loadmore_label','') : Text::_("MODARH_AJAXLOADMORE_LABEL");?></span> <i class="fas fa-plus"></i>
			</a>
		</div>
<?php
	endif;
endif;
?>

<?php
// 
// Pagination:
//
if($params->get('ajax_loadmore_type', 0) == 2):

	$list = ModArticlesHeadHelper::getPaginationList($module);

?>
	<div class="mod-intro-pagination">
		<ul class="pagination">

			<?php $link = $list["start"];?>
			<?php if($link->show): ?>
				<li class="page-item start<?php echo $link->disabled ? ' disabled' : '';?>">
					<a class="page-link" tabindex="0" <?php echo $link->config != '' ?  $link->config : '';?>>
						<i class="fas fa-angle-double-left"></i> 
						<?php if($params->get('ajax_pagination_labels',1)) echo $link->text; ?>
					</a>
				</li>
			<?php endif; ?>

			<?php $link = $list["previous"];?>
			<?php if($link->show): ?>
				<li class="page-item previous<?php echo $link->disabled ? ' disabled' : '';?>">
					<a class="page-link" tabindex="0" <?php echo $link->config != '' ?  $link->config : '';?>>
						<i class="fas fa-angle-left"></i>
						<?php if($params->get('ajax_pagination_labels',1)) echo $link->text; ?>
					</a>
				</li>
			<?php endif; ?>

			<?php
				// Seitenzahlen
				if($list["pages"]->show) :
					foreach($list["pages"]->options as $key => $link) :
			?>
						<li class="page-item page-<?php echo $key;?><?php echo $link->current ? ' active' : '';?>">
							<a class="page-link page-num" tabindex="0" <?php echo $link->config != '' ? $link->config : '';?>>
							<?php
								if($link->range) :
									// Nur anzeigen, dass es hier weiter geht
							?>
									<?php echo Text::_('MODARH_RANGELINK_LABEL','...');?>
							<?php
								else :
									// Seitennummer anzeigen
							?>
									<?php echo $link->text;?>
							<?php
								endif;
							?>
							</a>
						</li>
			<?php
					endforeach;
				endif;
			?>

			<?php $link = $list["next"];?>
			<?php if($link->show): ?>
				<li class="page-item next<?php echo $link->disabled ? ' disabled' : '';?>">
					<a class="page-link" tabindex="0" <?php echo $link->config != '' ? $link->config : '';?>>
						<?php if($params->get('ajax_pagination_labels',1)) echo $link->text; ?>
						<i class="fas fa-angle-right"></i>
					</a>
				</li>
			<?php endif; ?>

			<?php $link = $list["end"];?>
			<?php if($link->show): ?>
				<li class="page-item end<?php echo $link->disabled ? ' disabled' : '';?>">
					<a class="page-link" tabindex="0" <?php echo $link->config != '' ? $link->config : '';?>>
						<?php if($params->get('ajax_pagination_labels',1)) echo $link->text; ?>
						<i class="fas fa-angle-double-right"></i>
					</a>
				</li>
			<?php endif; ?>
		</ul>

		<?php
			// Seitenzähler (Seite x von y)
			if($params->get('ajax_pagination_pagecounter', 0)):
		?>
				<div class="page-counter">
					<?php echo Text::sprintf("JLIB_HTML_PAGE_CURRENT_OF_TOTAL", $list["pages"]->current, $list["pages"]->total);?>
				</div>
		<?php
			endif;
		?>
	</div>
<?php
endif;
?>