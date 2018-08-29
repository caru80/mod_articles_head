<?php
/**
 * @package        HEAD. Article Module
 * @version        1.8.1
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

if(!isset($layoutConf)) {
	$layoutConf = (object)array(
					"class_sfx"		=> "intro-default",
					"item_layout" 	=> "_item"
					);
}
?>

<?php
	if((bool)$params->get('ajax', false)) : // && $params->get('start', 0) > 0) :
	/**
		Asynchrone Anfrage von com_ajax, es wird nur der folgende Block ausgegeben:
	*/
?>
		<div class="mod-intro-items async" id="intro-<?php echo $module->id;?>-items-<?php echo $params->get('start',0);?>">
			<div class="list-row row-equal <?php echo $params->get('classnames_rows','');?>">
				<?php 
					foreach ($list as $item) : 
						require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_articles_head', $layoutConf->item_layout);
					endforeach;
				?>
			</div>
		</div>
		<?php
		/** 
			AJAX – Mehr laden
		*/
		if( (bool) $params->get('ajax_enable', false) ):
			require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath("mod_articles_head", "_ajaxloadmore");
		endif;

	else:
	/**
		Modul wurde „normal geladen”, das Template wird komplett ausgegeben:
	*/
?>
		<div id="mod-intro-<?php echo $module->id;?>" class="mod-intro<?php echo $moduleclass_sfx;?> <?php echo $layoutConf->class_sfx;?>">
			<?php
				/**
					Modul-Einleitungstext
				*/
				if($params->get('show_moduleintro',0)):
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
                if($params->get('ajax_enable', 0) && $params->get('ajax_filters', 0)) :
                    require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_articles_head', "_ajaxfilters");
                endif;
            ?> 

			<?php
				/** 
					Modul-Weiterlesen – Position "oben"
				*/
				if($params->get('module-link-position',0) >= 1):
			?>
					<div class="mod-intro-readmore-top">
					<?php
						require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_articles_head', "_modreadmore");
					?>
					</div>
			<?php
				endif;
			?>
			<div id="mod-intro-items-list-<?php echo $module->id;?>" class="mod-intro-items-list">
				<?php
					/**
						Neue Items, die per AJAX geladen werden, werden hier am Ende – nach div.mod-intro-items – eingefügt.
					*/					
				?>
				<div class="mod-intro-items" id="intro-<?php echo $module->id;?>-items-<?php echo $params->get('start',0);?>">
					<div class="list-row row-equal <?php echo $params->get('classnames_rows','');?>">
						<?php foreach ($list as $item) : ?>
							<?php require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_articles_head', $layoutConf->item_layout); ?>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
					/** 
						AJAX – Mehr laden
					*/
					if( (bool) $params->get('ajax_enable', false) ):
						require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath("mod_articles_head", "_ajaxloadmore");
					endif;	
				?>
			</div>
			<?php
				/** 
					Modul-Weiterlesen – Position "unten"
				*/
				if(($params->get('module-link-position',0) == 0)||($params->get('module-link-position',0) == 2)):
					require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_articles_head', "_modreadmore");
				endif;
			?>
		</div>
<?php
	endif;
?>