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
?>
<div id="filter-group-<?php echo $oneFilter->field_name;?>-<?php echo $module->id;?>" class="mod-intro-filter-group"<?php echo $oneFilter->group_data;?>>
	<?php
		if($oneFilter->label != ''):
	?>
			<div legend class="filter-group-label">
				<?php echo $oneFilter->label;?>
			</div>
	<?php
		endif;
	?>
	
	<select 
		class="filter-<?php echo $oneFilter->type;?> custom-select" 
		id="<?php echo $oneFilter->field_id;?>"
		name="<?php echo $oneFilter->field_name;?>" 
		<?php echo $oneFilter->multiple ? 'multiple' : '';?>
	>
	<?php
		if( ! $oneFilter->multiple ):
	?>
			<option value="" <?php echo !$oneFilter->is_active ? ' selected="selected"' : '';?>>
				<?php echo $oneFilter->option_all_label;?>
			</option>
	<?php
		endif;
	?>

	<?php
		foreach ($oneFilter->options as $option) :
			// Ist die Filteroption aktiv?
			if ($oneFilter->is_active)
			{
				if(is_array($oneFilter->current_value))
				{
					$active = in_array($option->raw_value, $oneFilter->current_value);
				}
				else if($option->raw_value == $oneFilter->current_value)
				{
					$active = true;
				}
			}
	?>
			<option id="<?php echo $option->id;?>" value="<?php echo $option->raw_value;?>"<?php echo $active ? ' selected="selected"' : '';?>>
				<?php echo $option->title;?>
				<?php echo $oneFilter->show_items ? "(" . $option->items_count . ")" : '';?>
			</option>
	<?php
		endforeach;
	?>
	</select>

	<?php
		if(!$oneFilter->multiple):
			// Ist keine mehrfachauswahl, und soll direkt abgeschickt werden. Weil wir an dem <select> keinen onclick handler lauschen lassen können (jedenfalls nicht sinnvoll) müssen wir etwas mehr Script-Magie einsetzen:
	?>
			<script>
				(function($){
					$('[name="<?php echo $oneFilter->field_name;?>"]').on('change', function()
					{
						$('#mod-intro-<?php echo $module->id;?>').modintroajax().applyFilterGroups();
					});
				})(jQuery);
			</script>
	<?php
		endif;
	?>
</div>
