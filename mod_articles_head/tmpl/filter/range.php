<?php
/**
 * @package        HEAD. Article Module
 * @version        1.10.0
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
$doc->addStylesheet(Uri::root() . 'media/mod_articles_head/css/nouislider.min.css');
?>
<div class="mod-intro-filter-group"<?php echo $oneFilter->group_data;?>>
	<?php
		if($oneFilter->label != ''):
	?>
			<div class="filter-group-label">
				<?php echo $oneFilter->label;?>
			</div>
	<?php
		endif;
	?>
	<div id="slider-<?php echo $oneFilter->field_name;?>"></div>
	<input name="<?php echo $oneFilter->field_name;?>" id="slider-<?php echo $oneFilter->field_name;?>-min" type="hidden" /> <?php // Wird den ausgewählen Minimalwert enthalten ?>
	<input name="<?php echo $oneFilter->field_name;?>" id="slider-<?php echo $oneFilter->field_name;?>-max" type="hidden" /> <?php // Wird den ausgewählten Maximalwert enthalten ?>
	<script>
		(function($) 
		{
			let slider      = document.getElementById('slider-<?php echo $oneFilter->field_name;?>'),
				minField    = document.getElementById('slider-<?php echo $oneFilter->field_name;?>-min'),
				maxField    = document.getElementById('slider-<?php echo $oneFilter->field_name;?>-max');

			noUiSlider.create(slider, {
				start   : [<?php echo floor($oneFilter->options[0]->raw_value);?>, <?php echo ceil(array_slice($oneFilter->options, -1)[0]->raw_value);?>],
				connect : [false, true, false],
				step    : 1,
				tooltips : true,
				range   : {
					'min': <?php echo floor($oneFilter->options[0]->raw_value);?>,
					'max': <?php echo ceil(array_slice($oneFilter->options, -1)[0]->raw_value);?>
				}
			});

			slider.noUiSlider.on('update', function(values, handle, unencoded, tap, positions) 
			{
				minField.value = values[0];
				maxField.value = values[1];
			});

			<?php
				if (!$oneFilter->multiple) :
					// Ist keine mehrfachauswahl, und soll direkt abgeschickt werden. Weil wir an dem <select> keinen onclick handler lauschen lassen können (jedenfalls nicht sinnvoll) müssen wir etwas mehr Script-Magie einsetzen:
			?>
				slider.noUiSlider.on('end', function()
				{
					$('#mod-intro-<?php echo $module->id;?>').modintroajax().applyFilterGroups();
				});
			<?php
				endif;
			?>
		})(jQuery);
	</script>
</div>
