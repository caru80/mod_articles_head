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
use Joomla\CMS\Language\Text;

use Joomla\CMS\Factory;

?>
<?php
$filterList = $Helper->getFilterList();
if(count($filterList)) :
	
	$show_apply_filters = false; // „Filter Anwenden” anzeigen?
?>
<div id="mod-intro-filters-<?php echo $module->id;?>" class="mod-intro-filters">
	<form>
	<?php
/*
if (!$params->get('render_partial', 0)) {
	echo "<div><pre>";
	var_dump($Helper->getState());
	echo "</pre></div>";
}
*/

		// Die Filter rendern:
		foreach ($filterList as $oneFilter) :
			// Hat dieser Filter Optionen zur Auswahl?
			//if(count($oneFilter->options)) :
				// Mehrfachauswahl vorhanden?
				if ($oneFilter->multiple) :
					$show_apply_filters = true;
				endif;

				// Das jeweilige Filter Template laden:
				require ModuleHelper::getLayoutPath('mod_articles_head', 'filter/' . $oneFilter->template);
			//endif;
		endforeach;
	?>
	<?php
		if ($show_apply_filters
			|| $params->get('ajax_reset_filters', 0)) :
			// Filter anwenden und zurücksetzen:
	?>
			<div class="mod-intro-filter-controls">
				<?php if ($show_apply_filters) : ?>
					<a tabindex="0" class="btn btn-primary set-filters"><?php echo Text::_('MODARH_FRONT_BTN_SUBMIT_FILTERS');?></a>
				<?php endif; ?>
				<?php if ($params->get('ajax_reset_filters', 0)) : ?>
					<a tabindex="0" class="btn btn-primary reset-filters"><?php echo Text::_('MODARH_FRONT_BTN_RESET_FILTERS');?></a>
				<?php endif; ?>
			</div>
	<?php
		endif;
	?>
	</form>
</div>
<?php
endif;
?>