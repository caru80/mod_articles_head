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
<div id="filter-group-<?php echo $oneFilter->field_name;?>-<?php echo $module->id;?>" class="mod-intro-filter-group btngroup"<?php echo $oneFilter->group_data;?>>
	<?php
		// Filter-Beschriftung
		if($oneFilter->label != ''):
	?>
			<div class="filter-group-label">
				<?php echo $oneFilter->label;?>
			</div>
	<?php
		endif;
	?>
	<div class="btn-group btn-group-toggle filter-<?php echo $oneFilter->type;?>" data-toggle="buttons">
		<?php
			// Option „Alle” (Diesen Filter zurücksetzen):
			if ( ! $oneFilter->multiple && ! $oneFilter->hide_option_all) :
		?>
				<label class="option-all btn btn-primary<?php echo !$oneFilter->is_active ? ' active' : '';?>">
					<input 
						type="radio" 
						name="<?php echo $oneFilter->field_name;?>" 
						id="<?php echo $oneFilter->field_name;?>-all-<?php echo $module->id;?>"
						value="" 
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
			// Filteroptionen ausgeben
			foreach ($oneFilter->options as $i => $option) :
				// Ist die Filteroption aktiv?
				$active = false;
				if ($oneFilter->is_active)
				{
					$active = in_array($option->raw_value, $oneFilter->current_value);
				}
		?>
				<label for="<?php echo $option->id;?>" class="btn btn-primary<?php echo $active ? ' active' : '';?>">
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
					<?php if($oneFilter->show_items): ?>
						<span class="badge"><?php echo $option->items_count;?></span>
					<?php endif; ?>
				</label>
		<?php
			endforeach;
		?>
	</div>
</div>
<script>
	<?php // Die Buttons der btn-group inaktiv machen, wenn die Filter zurückgesetzt werden. ?>
	(function($) {

		let module 	= '#mod-intro-<?php echo $module->id;?>',
			group 	= '#filter-group-<?php echo $oneFilter->field_name;?>-<?php echo $module->id;?>';

		$(function() {
			$(module).modintroajax().module.on('resetFilters', function() 
			{
				$(this).find('.btn-group .btn').each(function() 
				{
					if ($(this).hasClass('active')) 
					{
						<?php //$(this).button('toggle'); // Twitter Bootstrap! – NÄ! Weil das einen „change” Event auslöst, und dann NOCHMAL (!) eine Anfrage geschickt wird (siehe unten)!?>
						this.classList.remove('active'); <?php // jQuery.removeClass funktionierte nicht! (?!);?>
						$(this).children('input').prop('checked', false);
					}
				});
			});
		});

		<?php
		// Den Filter abschicken, wenn ein Wert ausgewählt wird:
		if (!$oneFilter->multiple) :
		?>
			$('[name="<?php echo $oneFilter->field_name;?>"]', group).on('change', function()
			{
				$(module).modintroajax().applyFilterGroups();
			});
		<?php
			endif;
		?>
	})(jQuery);
</script>
