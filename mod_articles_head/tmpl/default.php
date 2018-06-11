<?php
/**
	mod_articles_head
*/
defined('_JEXEC') or die;

if(!isset($layoutConf)) {
	$layoutConf = (object)array(
					"class_sfx"		=> "intro-default",
					"item_layout" 	=> "_item"
					);
}

// Modul-Weiterlesen
$moduleReadmore = false;
if( $params->get('linkeditem','') != '' )
{
	$menuitem = JFactory::getApplication()->getMenu()->getItem($params->get('linkeditem',''));
	$moduleReadmore = JRoute::_($menuitem->link.'&Itemid='.$menuitem->id);

}
elseif( $params->get('linkcategory',0) != 0 )
{
	$categories = $params->get('catid', false); // wg. Notice: Strict Standards...
	$moduleReadmore = JRoute::_(ContentHelperRoute::getCategoryRoute(reset($categories)));
}
?>

<?php
	if((bool)$params->get('ajax', false) && $params->get('start', 0) > 0) :
	/**
		Asynchrone Anfrage von com_ajax, es wird nur der folgende Block ausgegeben:
	*/
?>
		<div class="mod-intro-items async" id="intro-<?php echo $module->id;?>-items-<?php echo $params->get('start',0);?>">
			<div class="list-row row-equal">
				<?php 
					foreach ($list as $item) : 
						require JModuleHelper::getLayoutPath('mod_articles_head', $layoutConf->item_layout);
					endforeach;
				?>
			</div>
		</div>
		<?php
		/** 
			AJAX – Mehr laden
		*/
		if( (bool) $params->get('ajax_loadmore', false) ):
			require JModuleHelper::getLayoutPath("mod_articles_head", "_ajaxloadmore");
		endif;

	else:
	/**
		Modul wurde „normal geladen”, das Template wird komplett ausgegeben:
	*/
?>
		<div id="mod-intro-<?php echo $module->id;?>" class="mod-intro<?php echo $moduleclass_sfx;?> <?php echo $layoutConf->class_sfx;?>">
			<?php
				/**
					Modul-Einleitungstext
				*/
				if($params->get('show_moduleintro',0)):
			?>
					<div class="mod-intro-introtext">
						<?php
							echo $params->get('moduleintro');
						?>
					</div>
			<?php
				endif;
			?>

			<?php
				/** 
					Modul-Weiterlesen – Position "oben"
				*/
				if($moduleReadmore && $params->get('module-link-position',0) >= 1):
			?>
					<div class="mod-intro-readmore-top">
					<?php
						require JModuleHelper::getLayoutPath('mod_articles_head', "_modreadmore");
					?>
					</div>
			<?php
				endif;
			?>
			<div id="mod-intro-items-list-<?php echo $module->id;?>" class="mod-intro-items-list">
				<?php
					/**
						Neue Items, die per AJAX geladen werden, werden hier am Ende – nach div.mod-intro-items – eingefügt.
					*/					
				?>
				<div class="mod-intro-items" id="intro-<?php echo $module->id;?>-items-<?php echo $params->get('start',0);?>">
					<div class="list-row row-equal">
						<?php foreach ($list as $item) : ?>
							<?php require JModuleHelper::getLayoutPath('mod_articles_head', $layoutConf->item_layout); ?>
						<?php endforeach; ?>
					</div>
				</div>
				<?php
					/** 
						AJAX – Mehr laden
					*/
					if( (bool) $params->get('ajax_loadmore', false) ):
						require JModuleHelper::getLayoutPath("mod_articles_head", "_ajaxloadmore");
					endif;	
				?>
			</div>
			<?php
				/** 
					Modul-Weiterlesen – Position "unten"
				*/
				if(($moduleReadmore && $params->get('module-link-position',0) == 0)||($moduleReadmore && $params->get('module-link-position',0) == 2)):
					require JModuleHelper::getLayoutPath('mod_articles_head', "_modreadmore");
				endif;
			?>
		</div>
<?php
	endif;	
?>