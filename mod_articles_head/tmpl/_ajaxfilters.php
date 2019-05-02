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
use Joomla\CMS\Language\Text;

// -- Die Filtereinstellungen abholen:
$ajaxFilterSettings = $params->get('ajax_filter', array());

// -- Die Filterliste aufbauen:
$ajaxFilterList = array();
foreach($ajaxFilterSettings as $filterParams)
{
	$ajaxFilterList[] = ModArticlesHeadHelper::getFilter($module, $filterParams);
}
$showFilterControls = false;

?>
<form name="mod-intro-filters-<?php echo $module->id;?>" id="mod-intro-filters-<?php echo $module->id;?>" class="mod-intro-filters">
<?php
    // Die Filter rendern:
	foreach ($ajaxFilterList as $i => $oneFilter) :

        // Mehrfachauswahl vorhanden?
		if($oneFilter->multiple):
            $showFilterControls = true;
		endif;

		// Das jeweilige Filter Template laden:
	    require ModuleHelper::getLayoutPath('mod_articles_head', 'filter/' . $oneFilter->template);

	endforeach;
?>
<?php
    if ($showFilterControls) :
        // Filter anwenden und zurücksetzen:
?>
		<div class="mod-intro-filter-controls">
            <a tabindex="0" class="set-filters"><?php echo Text::_('MODARH_FRONT_BTN_SUBMIT_FILTERS');?></a>
            <a tabindex="0" class="reset-filters"><?php echo Text::_('MODARH_FRONT_BTN_RESET_FILTERS');?></a>
		</div>
<?php
	endif;
?>
</form>
