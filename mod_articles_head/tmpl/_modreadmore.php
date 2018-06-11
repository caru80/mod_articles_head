<?php
/**
    mod_articles_head
    
    Modul-Weiterlesen
*/
defined('_JEXEC') or die;
?>
<div class="mod-intro-readmore">
	<a href="<?php echo $moduleReadmore;?>" class="btn btn-primary more">
		<span><?php echo $params->get('module_readmore_label','') != '' ? $params->get('module_readmore_label','') : JText::_("MOD_ARTICLES_HEAD_MODULEREADMORE_LABEL_FRONT");?></span> <i class="fas fa-chevron-right"></i>
	</a>
</div>