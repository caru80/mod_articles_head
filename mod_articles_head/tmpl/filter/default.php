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
<div id="filter-group-<?php echo $oneFilter->field_name;?>-<?php echo $module->id;?>" class="mod-intro-filter-group default"<?php echo $oneFilter->group_data;?>>
	<?php
		if($oneFilter->label != ''):
	?>
			<div class="filter-group-label">
				<?php echo $oneFilter->label;?>
			</div>
	<?php
		endif;
	?>

	<?php
		// Option „Alle” (Diesen Filter zurücksetzen)
		if( ! $oneFilter->multiple 
			&& ! $oneFilter->hide_option_all) :
	?>
			<label class="option-all<?php echo !$oneFilter->is_active ? ' active' : '';?>">
				<input 
					type="radio" 
					value="" 
					name="<?php echo $oneFilter->field_name;?>" 
					id="<?php echo $oneFilter->field_name;?>-all-<?php echo $module->id;?>"
					<?php 
						echo !$oneFilter->is_active ? ' checked="checked"' : '';
					?>
				/>
				<?php echo $oneFilter->option_all_label;?>
			</label>
	<?php
		endif;
	?>

	<?php
		foreach ($oneFilter->options as $i => $option) :
			// Ist die Filteroption aktiv?
			$active = false;
			if ($oneFilter->is_active)
			{
				$active = in_array($option->raw_value, $oneFilter->current_value);
			}
	?>
			<label for="<?php echo $option->id;?>"<?php echo $active ? ' class="active"' : '';?>>
				<input 
					type="<?php echo $oneFilter->multiple ? 'checkbox' : 'radio';?>" 
					value="<?php echo $option->raw_value;?>" 
					name="<?php echo $oneFilter->field_name;?>" 
					id="<?php echo $option->id;?>"
					<?php 
						// Option aktiv...
						echo $active ? ' checked="checked"' : '';
					?>
				/> 
				<?php echo $option->title;?>
			</label>
	<?php
		endforeach;
	?>
	<?php
        if(!$oneFilter->multiple):
            // Ist keine mehrfachauswahl, und soll direkt abgeschickt werden. Weil wir an dem <input> und dem <label> keinen onclick handler lauschen lassen können (jedenfalls nicht sinnvoll) müssen wir etwas mehr Script-Magie einsetzen:
    ?>
            <script>
				(function($)
				{
					let module 	= '#mod-intro-<?php echo $module->id;?>',
						group 	= '#filter-group-<?php echo $oneFilter->field_name;?>-<?php echo $module->id;?>';

                    $('[name="<?php echo $oneFilter->field_name;?>"]', group).on('change', function()
                    {
                        $(module).modintroajax().applyFilterGroups();
                    });
                })(jQuery);
            </script>
    <?php
        endif;
    ?>
</div>
