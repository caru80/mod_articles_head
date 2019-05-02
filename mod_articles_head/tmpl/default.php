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

use Joomla\CMS\Helper\ModuleHelper;


if(!isset($layoutConf)) {
	$layoutConf = (object)array(
					"class_sfx"		=> "intro-default",		// CSS suffix für das zu rendernde Modul
					"item_layout" 	=> "_item"				// Template-Name für Beiträge/Items
					);
}
?>

<?php
	if ($params->get('ajax', 0)) :
	//
	// Asynchrone Anfrage von com_ajax, es wird nur der folgende Block ausgegeben:
	//
?>
		<div class="mod-intro-items async" id="intro-<?php echo $module->id;?>-items-<?php echo $params->get('start', 0);?>">
			<div class="list-row <?php echo $params->get('classnames_rows', '');?>">
				<?php 
					foreach ($list as $item) : 
						require ModuleHelper::getLayoutPath('mod_articles_head', $layoutConf->item_layout);
					endforeach;
				?>
			</div>
		</div>
		<?php
		//
		// AJAX: „Mehr laden” oder Paginierung
		// 
		if ($params->get('ajax_enable', 0)) :
			require ModuleHelper::getLayoutPath("mod_articles_head", "_ajaxloadmore");
		endif;

	else:
	//
	//	Modul wurde „normal geladen”, das Template wird komplett ausgegeben:
	//
?>
		<div id="mod-intro-<?php echo $module->id;?>" class="mod-intro<?php echo $moduleclass_sfx;?> <?php echo $layoutConf->class_sfx;?>">
			<?php
				//
				// Modul-Einleitungstext
				//
				if ($params->get('show_moduleintro', 0)) :
			?>
					<div class="mod-intro-introtext">
						<?php
							echo $params->get('moduleintro');
						?>
					</div>
			<?php
				endif;
			?>
            
			<?php
				//
				// AJAX-Filter
				//
				if ($params->get('ajax_enable', 0) 
						&& $params->get('ajax_filters', 0)) :
                    require ModuleHelper::getLayoutPath('mod_articles_head', "_ajaxfilters");
                endif;
            ?> 

			<?php
				//
				// 	Modul-Weiterlesen – Position "oben"
				// 
				if ($params->get('module_readmore_type', '') !== '' 
						&& $params->get('module_readmore_position', '') === 'top'
						|| $params->get('module_readmore_position', '') === 'both') :
					require ModuleHelper::getLayoutPath('mod_articles_head', "_modreadmore");
				endif;
			?>
			<div id="mod-intro-items-list-<?php echo $module->id;?>" class="mod-intro-items-list">
				<?php
					//
					// Neue Items, die per AJAX geladen werden, werden hier hinein, nach div.mod-intro-items, eingefügt.
					//
				?>
				<div class="mod-intro-items" id="intro-<?php echo $module->id;?>-items-<?php echo $params->get('start',0);?>">
					<div class="list-row <?php echo $params->get('classnames_rows','');?>">
						<?php foreach ($list as $item) : ?>
							<?php require ModuleHelper::getLayoutPath('mod_articles_head', $layoutConf->item_layout); ?>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
					//
					// AJAX: „Mehr laden” oder Paginierung
					// 
					if ($params->get('ajax_enable', 0)) :
						require ModuleHelper::getLayoutPath("mod_articles_head", "_ajaxloadmore");
					endif;	
				?>
			</div>
			<?php
				//
				// 	Modul-Weiterlesen – Position „unten”
				// 
				if ($params->get('module_readmore_type', '') !== '' 
						&& $params->get('module_readmore_position', '') === 'bottom'
						|| $params->get('module_readmore_position', '') === 'both') :
					require ModuleHelper::getLayoutPath('mod_articles_head', "_modreadmore");
				endif;
			?>
		</div>
<?php
	endif;
?>