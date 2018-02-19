<?php
/**

	mod_articles_head

 */

defined('_JEXEC') or die;

// Modul-Weiterlesen
$moduleReadmore = false;
if( $params->get('linkeditem','') != '' )
{
	$menuitem = JFactory::getApplication()->getMenu()->getItem($params->get('linkeditem',''));
	$moduleReadmore = JRoute::_($menuitem->link.'&Itemid='.$menuitem->id);

}
elseif( $params->get('linkcategory',0) != 0 )
{
	$moduleReadmore = JRoute::_( ContentHelperRoute::getCategoryRoute($params->get('catid')) );
}
?>

<?php
	if( (bool) $params->get('ajax', false) && $params->get('start', 0) > 0):
		/*
			Von /templates/head/ajax_loadmodule.php wurde der Parameter 'ajax' auf 1 gesetzt, und es wird nur dieser Block ausgegeben:	
		*/	
?>
		<div class="mod-intro-items async" id="intro-<?php echo $module->id;?>-items-<?php echo $params->get('start',0);?>">
			<div class="list-row">
				<?php foreach ($list as $item) : ?>
					<?php require JModuleHelper::getLayoutPath('mod_articles_head', '_item'); ?>
				<?php endforeach; ?>
			</div>
		</div>
		<?php
			// AJAX – Mehr laden
			if( (bool) $params->get('ajax', false) ):
				
				$start  = $params->get('start',0) + $params->get('count', 4);

				if( $start < $fullItemsCount ):
		?>
					<div class="mod-intro-loadmore">
						<a tabindex="0" data-ajax='{"module":true,"id":<?php echo $module->id;?>,"s":<?php echo $start;?>,"target":"#mod-intro-items-list-<?php echo $module->id;?>","rtrigger":true,"chrome":"none"}'>
							<?php
								if( $params->get('ajax_loadmore_label','') != '' ):
							?>
									<span><?php echo $params->get('ajax_loadmore_label');?></span>
							<?php
								endif;	
							?>
							<i></i>
						</a>
					</div>
		<?php
				endif;
			endif;	
		?>

<?php
	else:	
	
		/*
			Das Template wird komplett ausgegeben	
		*/
?>

		<div class="mod-intro<?php echo $moduleclass_sfx; ?>">
			<?php
				if( $params->get('moduleintro','') != '' ):
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
				// Modul-Weiterlesen Position "Oben"
				if($moduleReadmore && $params->get('module-link-position',0) >= 1):
			?>
					<div class="mod-intro-readmore mod-intro-readmore">
						<a href="<?php echo $route;?>" class="icon more">
							<?php
								if( $params->get('linklabel','') != '' ):
							?>
									<span><?php echo $params->get('linklabel');?></span>
							<?php
								endif;	
							?>
							<i></i>
						</a>
					</div>
			<?php
				endif;	
			?>
		
			<div id="mod-intro-items-list-<?php echo $module->id;?>">
				<?php
					/*
						Neue Items, die per AJAX geladen werden, werden hier eingefügt (am Ende, nach div.mod-intro-items).
					*/					
				?>
				<div class="mod-intro-items" id="intro-<?php echo $module->id;?>-items-<?php echo $params->get('start',0);?>">
					<div class="list-row">
						<?php foreach ($list as $item) : ?>
							<?php require JModuleHelper::getLayoutPath('mod_articles_head', '_item'); ?>
						<?php endforeach; ?>
					</div>
				</div>
				
				<?php
					/* 
						AJAX – mehr laden
						
						Dieser Link wird nach dem anglicken automatisch von $app.ajax entfernt.
					*/
					if( (bool) $params->get('ajax', false) ):
					
						$start = $params->get('count',4);
						
						if( $start < $fullItemsCount ):
				?>
							<div class="mod-intro-loadmore">
								<a tabindex="0" data-ajax='{"module":true,"id":<?php echo $module->id;?>,"s":<?php echo $start;?>,"target":"#mod-intro-items-list-<?php echo $module->id;?>","rtrigger":true,"chrome":"none"}'>
									<?php
										if( $params->get('ajax_loadmore_label','') != '' ):
									?>
											<span><?php echo $params->get('ajax_loadmore_label');?></span>
									<?php
										endif;	
									?>
									<i></i>
								</a>
							</div>
				<?php
						endif;
					endif;	
				?>
			</div>
			
			<?php
				// Modul-Weiterlesen unten
				if(($moduleReadmore && $params->get('module-link-position',0) == 0)||($moduleReadmore && $params->get('module-link-position',0) == 2)):
			?>
					<div class="mod-intro-readmore">
						<a href="<?php echo $route;?>">
							<?php
								if( $params->get('linklabel','') != '' ):
							?>
									<span><?php echo $params->get('linklabel');?></span>
							<?php
								endif;	
							?>
							<i></i>
						</a>
					</div>
			<?php
				endif;
			?>
		</div>
<?php
	endif;	
?>