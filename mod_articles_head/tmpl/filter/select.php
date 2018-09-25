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
			<div legend class="filter-group-label">
				<?php echo $oneFilter->label;?>
			</div>
	<?php
		endif;
    ?>
    
    <select 
        class="filter-<?php echo $oneFilter->type;?>" 
        name="<?php echo $oneFilter->field_name;?>" 
        <?php echo $oneFilter->multiple ? 'multiple' : '';?>
    >
    <?php
        if( ! $oneFilter->multiple ):
    ?>
            <option value=""><?php echo JText::_('JALL');?></option>
    <?php
        endif;
    ?>

    <?php
        foreach($oneFilter->options as $option):
    ?>
            <option value="<?php echo $option->raw_value;?>">
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
                        $('#mod-intro-<?php echo $module->id;?>').modintroajax().applyFitlerGroups();
                    });
                })(jQuery);
            </script>
    <?php
        endif;
    ?>
</div>
