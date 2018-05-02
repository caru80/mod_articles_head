				
<?php
/**
    mod_articles_head
    
    AJAX – mehr laden.
*/
defined('_JEXEC') or die;
?>
<?php
$start = $params->get('start',0) + $params->get('count', 4);
if( $start < $fullItemsCount ):
?>
    <div class="mod-intro-loadmore">
        <a tabindex="0" class="btn btn-primary" data-modintroajax='{"url":"<?php echo JUri::root();?>index.php","id":<?php echo $module->id;?>,"s":<?php echo $start;?>,"target":"#mod-intro-items-list-<?php echo $module->id;?>"}'>
            <span><?php echo $params->get('ajax_loadmore_label','') != '' ? $params->get('ajax_loadmore_label','') : JText::_("MOD_ARTICLES_HEAD_AJAXLOADMORE_LABEL");?></span> <i class="fas fa-plus"></i>
        </a>
    </div>
<?php
endif;
?>