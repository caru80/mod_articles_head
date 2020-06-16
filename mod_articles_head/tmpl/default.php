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
			require ModuleHelper::getLayoutPath('mod_articles_head', '_ajaxfilters');
		endif;
	?> 

	<?php
		//
		// 	Modul-Weiterlesen – Position "oben"
		// 
		if ($params->get('module_readmore_type', '') !== '' 
				&& $params->get('module_readmore_position', '') === 'top'
				|| $params->get('module_readmore_position', '') === 'both') :
			require ModuleHelper::getLayoutPath('mod_articles_head', '_modreadmore');
		endif;
	?>
	
	<div id="mod-intro-items-list-<?php echo $module->id;?>" class="mod-intro-items-list">
		<?php
			// 
			// Liste der Items und Paginierung bzw. "Mehr Laden"
			// Wird bei AJAX aufruf ersetzt, oder neuer Inhalt wird hier angehängt.
			//
			require ModuleHelper::getLayoutPath("mod_articles_head", "_itemlist");
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