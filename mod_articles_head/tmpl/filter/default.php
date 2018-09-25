<?php
/**
 * @package        HEAD. Article Module
 * @version        1.8.5
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


	<?php
		// „Alle” (Diesen Filter zurücksetzen):
		if( ! $oneFilter->multiple):
	?>
			<label data-modintroajax='<?php echo $oneFilter->reset_option->ajax_json;?>'>
				<input type="radio" name="<?php echo $oneFilter->field_name;?>" value="" /> <?php echo JText::_('JALL');?>
			</label>
	<?php
		endif;
	?>

	<?php
		foreach($oneFilter->options as $option):
	?>
			<label 
				<?php
					if( ! $oneFilter->multiple) : 
						// Dieser Filter hat keine Mehrfachauswahl und wird beim anklicken gesetzt.
				?>
						data-modintroajax='<?php echo $option->ajax_json;?>'
				<?php
					endif;
				?>
			>
				<input 
					type="<?php echo $oneFilter->multiple ? 'checkbox' : 'radio';?>" 
					value="<?php echo $option->raw_value;?>" 
					name="<?php echo $oneFilter->field_name;?>" 
				/> 
				<?php echo $option->title;?>
				<?php echo $oneFilter->show_items ? $option->items_count : '';?>
			</label>
	<?php
		endforeach;
	?>
</div>
