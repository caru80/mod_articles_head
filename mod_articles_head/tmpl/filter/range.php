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

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

$doc = Factory::getApplication()->getDocument();
$doc->addScript(Uri::root() . 'media/mod_articles_head/js/nouislider.min.js');
$doc->addScript(Uri::root() . 'media/mod_articles_head/js/wNumb.min.js');
$Helper->addStylesheet('nouislider.min.css');

// Startwerte für den Slider
if($oneFilter->current_value) // Aus dem Sitzungsspeicher (der User hat etwas gewählt, oder ein anderer Filter wurde abgeschickt).
{
	$slider_start_min = $oneFilter->current_value[0];
	$slider_start_max = $oneFilter->current_value[1];
}
else // Aus den Standardwerten (es wurde noch Nichts gewählt).
{
	$slider_start_min = $oneFilter->options[0]->raw_value;
	$slider_start_max = end($oneFilter->options)->raw_value;
}

foreach($oneFilter->options as $i => $option)
{	
	if($option->raw_value <= $slider_start_min)
	{
		$min_items = $option->items_count;
	}
	else if($option->raw_value >= $slider_start_max)
	{
		$max_items = $option->items_count;
	}
}


?>
<div id="filter-group-<?php echo $oneFilter->field_name;?>-<?php echo $module->id;?>" class="mod-intro-filter-group range"<?php echo $oneFilter->group_data;?>>
	<?php
		if($oneFilter->label != ''):
	?>
			<div class="filter-group-label">
				<?php echo $oneFilter->label;?>
			</div>
	<?php
		endif;
	?>
	<div id="slider-<?php echo $oneFilter->field_id;?>"></div>
	<input name="<?php echo $oneFilter->field_name;?>" id="<?php echo $oneFilter->field_id;?>-min" type="hidden" /> <?php // Wird den ausgewählen Minimalwert enthalten ?>
	<input name="<?php echo $oneFilter->field_name;?>" id="<?php echo $oneFilter->field_id;?>-max" type="hidden" /> <?php // Wird den ausgewählten Maximalwert enthalten ?>
	
<?php
	if(count($oneFilter->options) > 1) :
?>	
	<script>
		(function($) 
		{
			let module 		= 'mod-intro-<?php echo $module->id;?>',
				group 		= '#filter-group-<?php echo $oneFilter->field_name;?>-<?php echo $module->id;?>',
				slider      = document.getElementById('slider-<?php echo $oneFilter->field_id;?>'),
				minField    = document.getElementById('<?php echo $oneFilter->field_id;?>-min'),
				maxField    = document.getElementById('<?php echo $oneFilter->field_id;?>-max');

			noUiSlider.create(slider, {
				<?php
					if($oneFilter->range_round_numbers) :
				?>
					start   : [<?php echo round($slider_start_min - 0.5);?>, <?php echo round($slider_start_max + 0.5);?>],
				<?php
					else :
				?>
					start   : [<?php echo $slider_start_min;?>, <?php echo $slider_start_max;?>],
				<?php
					endif;
				?>
				connect 	: [false, true, false],
				step    	: 1,
				tooltips 	: [wNumb(<?php echo $oneFilter->range_format;?>), wNumb(<?php echo $oneFilter->range_format;?>)],
				range   	: {
					<?php
						if((bool)$oneFilter->range_round_numbers) :
					?>
					'min': <?php echo round($oneFilter->options[0]->raw_value - 0.5);?>,
					'max': <?php echo round(end($oneFilter->options)->raw_value + 0.5);?>
					<?php
					else :
					?>
					'min': <?php echo $oneFilter->options[0]->raw_value;?>,
					'max': <?php echo end($oneFilter->options)->raw_value;?>
					<?php
						endif;
					?>	
				}
			});

			<?php // Werte des Sliders zum absenden vorbereiten ?>
			slider.noUiSlider.on('update', function(values, handle, unencoded, tap, positions) 
			{
				minField.value = parseFloat(values[0]);
				maxField.value = parseFloat(values[1]);
			});

			<?php // Slider zurücksetzen ?>
			$('#' + module).on('resetFilters', function () 
			{
				let defaults = [this.noUiSlider.options.range.min, this.noUiSlider.options.range.max];
				this.noUiSlider.set(defaults);
			}.bind(slider));

			<?php
				if (!$oneFilter->multiple) :
					// Ist keine mehrfachauswahl, und soll direkt abgeschickt werden.
			?>
				slider.noUiSlider.on('end', function()
				{
					$('#' + module).modintroajax().applyFilterGroups();
				});
			<?php
				endif;
			?>
		})(jQuery);
	</script>
<?php
	endif;
?>
</div>
