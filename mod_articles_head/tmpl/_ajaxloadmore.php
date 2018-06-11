				
<?php
/**
	mod_articles_head
	
	AJAX – mehr laden.
*/
defined('_JEXEC') or die;

// -- AJAX-Request Konfiguration
$config = (object) [
			'url' 		=> JUri::root() . 'index.php',
			'id'		=> $module->id,
			's' 		=> $params->get('start',0) + $params->get('count', 4),
			'target' 	=> '#mod-intro-items-list-' . $module->id 
		];
// -- Post-Animationen
if($params->get('ajax_post_animations',0)) {
	$config->animate 	= true;
	$config->aniclass 	= $params->get('ajax_post_animation_class','');
	$config->aniname 	= $params->get('ajax_post_animation_name','');
}
?>
<?php
if( $config->s < $fullItemsCount ):
?>
	<div class="mod-intro-loadmore">
		<a tabindex="0" class="btn btn-primary" data-modintroajax='<?php echo json_encode($config);?>'>
			<span><?php echo $params->get('ajax_loadmore_label','') != '' ? $params->get('ajax_loadmore_label','') : JText::_("MOD_ARTICLES_HEAD_AJAXLOADMORE_LABEL");?></span> <i class="fas fa-plus"></i>
		</a>
	</div>
<?php
endif;
?>