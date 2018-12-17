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


/**
 * Renders the pagination list
 *
 * @param   array   $list  Array containing pagination information
 *
 * @return  string         HTML markup for the full pagination object
 *
 * @since   3.0
 */
function pagination_list_render($list)
{
	// Calculate to display range of pages
	$currentPage = 1;
	$range = 1;
	$step = 5;
	foreach ($list as $k => $page)
	{
		if (!$page->current)
		{
			$currentPage = $k;
		}
	}
	if ($currentPage >= $step)
	{
		if ($currentPage % $step == 0)
		{
			$range = ceil($currentPage / $step) + 1;
		}
		else
		{
			$range = ceil($currentPage / $step);
		}
	}

	foreach ($list['pages'] as $k => $page)
	{
		if (in_array($k, range($range * $step - ($step + 1), $range * $step)))
		{
			if (($k % $step == 0 || $k == $range * $step - ($step + 1)) && $k != $currentPage && $k != $range * $step - $step)
			{
				$page['data'] = preg_replace('#(<a.*?>).*?(</a>)#', '$1...$2', $page['data']);
			}
		}

		$html .= $page['data'];
	}

	return $html;
}

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
	<div class="mod-intro-pagination">
		<ul class="pagination">
			
			<?php $link = $list["start"];?>
			<?php if($link->show): ?>
				<li class="page-item start<?php echo $link->disabled ? ' disabled' : '';?>">
					<a<?php echo $link->config != '' ? ' ' . $link->config : '';?> class="page-link">
						<i class="fas fa-angle-double-left"></i> 
						<?php if($params->get('ajax_pagination_labels',1)) echo $link->text; ?>
					</a>
				</li>
			<?php endif; ?>

			<?php $link = $list["previous"];?>
			<?php if($link->show): ?>
				<li class="page-item previous<?php echo $link->disabled ? ' disabled' : '';?>">
					<a<?php echo $link->config != '' ? ' ' . $link->config : '';?> class="page-link">
						<i class="fas fa-angle-left"></i>
						<?php if($params->get('ajax_pagination_labels',1)) echo $link->text; ?>
					</a>
				</li>
			<?php endif; ?>

			<?php
				if($list["pages"]->show) :
					foreach($list["pages"]->options as $key => $link) :
			?>
						<li class="page-item page-<?php echo $key;?><?php echo $link->current ? ' active' : '';?>">
							<a<?php echo $link->config != '' ? ' ' . $link->config : '';?> class="page-link">
								<?php echo $link->text;?>
							</a>
						</li>
			<?php
					endforeach;
				endif;
			?>

			<?php $link = $list["next"];?>
			<?php if($link->show): ?>
				<li class="page-item next<?php echo $link->disabled ? ' disabled' : '';?>">
					<a<?php echo $link->config != '' ? ' ' . $link->config : '';?> class="page-link">
					<?php if($params->get('ajax_pagination_labels',1)) echo $link->text; ?>
					<i class="fas fa-angle-right"></i>
					</a>
				</li>
			<?php endif; ?>

			<?php $link = $list["end"];?>
			<?php if($link->show): ?>
				<li class="page-item end<?php echo $link->disabled ? ' disabled' : '';?>">
					<a<?php echo $link->config != '' ? ' ' . $link->config : '';?> class="page-link">
						<?php if($params->get('ajax_pagination_labels',1)) echo $link->text; ?>
						<i class="fas fa-angle-double-right"></i>
					</a>
				</li>
			<?php endif; ?>

		</ul>
	</div>
<?php
endif;
?>