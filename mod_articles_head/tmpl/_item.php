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

use \Joomla\Registry\Registry;
use \Joomla\CMS\Helper\ModuleHelper;
use \Joomla\CMS\Layout\LayoutHelper;

// -- Artikel-Parameter und "X-Fields"
$attribs 	= new Registry($item->attribs);
// -- Bilder
$images 	= json_decode($item->images);

/** 
	Joomla 3.7+ Eigene Felder/Custom Fields
	
	Dazu in den Moduleinstellungen das Triggern der Events einschalten!

	Ausgabe Inhalt:
	
	echo $item->customField['FELDNAME']->value;
*/
if ($params->get('triggerevents',0)) 
{
	JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
	foreach($item->jcfields as $field)
	{
		$item->jcfields[$field->name] = $field;
	}
}

//
// Auf „.item-wrapper” werden die Animationen angewendet !!!
//
?>
<div class="item-wrapper <?php echo $params->get('classnames_cols','');?>">
	<article class="item <?php echo $params->get('classnames_items','');?>" itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
		<meta itemprop="inLanguage" content="<?php echo ($item->language === '*') ? \Joomla\CMS\Factory::getConfig()->get('language') : $item->language; ?>" />
		<?php
			// Vorschaubild
			if ($params->get('preview-image', 1)
				&& $item->imageSrc != '') :
		?>
				<figure class="item-image-intro">
					<img src="<?php echo $item->imageSrc;?>" alt="<?php echo $item->imageAlt;?>" />
					<?php
						if($item->imageCaption != '') :
					?>
							<figcaption>
								<?php echo $item->imageCaption;?>
							</figcaption>
					<?php
						endif;
					?>

					<?php
						// -- Modul-Layout Vorschauvideo
						if ($params->get('introvideos',0)):
							require ModuleHelper::getLayoutPath('mod_articles_head', '_itemvideo');
						endif;
					?>
				</figure>
		<?php
			endif;
		?>

		<?php
			// Protoslider Layout
			echo LayoutHelper::render('head.protoslider', $item);
		?>

		<?php
			// Titel
			if( $params->get('item_title',false) && ( $attribs->get('show_title', false) == 1 || $attribs->get('show_title', false) == '' ) ):
				$htag = $params->get('item_heading','h3');
		?>
				<header itemprop="name" class="item-header">
					<<?php echo $htag;?> class="item-title">
						<?php
							if( (bool)$params->get('link_titles', 0)
								&& ($params->get('readmore', 0) && $item->readmore > 0 
									|| $params->get('readmore', 0) && $params->get('force_readmore', 0))) :
						?>
								<a href="<?php echo $item->link;?>">
						<?php
							endif;
						?>
									<?php echo $item->title;?>
						<?php
							if( (bool)$params->get('link_titles', 0)
								&& ($params->get('readmore', 0) && $item->readmore > 0 
									|| $params->get('readmore', 0) && $params->get('force_readmore', 0))) :
						?>
								</a>
						<?php
							endif;
						?>
					</<?php echo $htag;?>>
				</header>
		<?php
				echo $item->afterDisplayTitle;
			endif;
		?>
		
		<?php
			// Info-Block (Datum, Autor etc.)
			if ($params->get('show_infoblock', 0)) :
		?>
				<aside class="item-infoblock" role="contentinfo">
					<?php echo LayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
				</aside>
		<?php
			endif;
		?>

		<?php
			// Schlagworte anzeigen
			if ($params->get('show_tags', 0)) :
		?>
				<section class="item-tags">
					<?php require ModuleHelper::getLayoutPath('mod_articles_head', '_tags'); ?>
				</section>
		<?php
			endif;
		?>

		<?php 
			echo $item->beforeDisplayContent;

			if($params->get('introtext', 1)):
		?>
				<section class="item-introtext" itemprop="articleBody">
					<?php echo $item->introtext; ?>
				</section>
		<?php
			endif;

			echo $item->afterDisplayContent;
		?>
		<?php
			if($params->get('readmore', 0) && $item->readmore > 0 
				|| $params->get('readmore', 0) && $params->get('force_readmore', 0)) :
		?>
			<footer class="item-footer">
				<?php require ModuleHelper::getLayoutPath('mod_articles_head', '_readmore'); ?>
			</footer>
		<?php
			endif;
		?>
	</article>
</div>
