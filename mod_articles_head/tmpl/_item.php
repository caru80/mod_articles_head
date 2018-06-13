<?php
/**
 * @package        HEAD. Article Module
 * @version        1.7.4
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

// -- Artikel Parameter und "X-Fields"
$attribs 	= new JRegistry( $item->attribs );
// -- Bilder
$images 	= json_decode( $item->images );

/** 
	Joomla 3.7 Eigene Felder/Custom Fields
	
	Dazu in den Moduleinstellungen das Triggern der Events einschalten!

	Ausgabe Inhalt:
	
	echo $item->customField['FELDNAME']->value;
*/
if($params->get('triggerevents',0)) {
	JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
	foreach($item->jcfields as $field)
	{
		$item->jcfields[$field->name] = $field;
	}
}

/**
    Weiterlesen-Link
    $item_readmore_url ist entweder ein string mit einem URL. Dabei entweder der standard Weiterlesen-URL oder der aus den X-Fields-Overrides ermittelte URL.
    Wenn gar kein URL ermittlet werden kann ist $item_readmore_url ein leerer String. 
*/
$item_readmore_url = ModArticlesHeadHelper::getReadmoreUrl($item);
// -- target="_blank"; Boolesch
$item_readmore_blank = (bool) $attribs->get('xfields_readmore_blank',0);

?>
<div class="item-column col-equal <?php echo $params->get('classnames_cols','');?>">
	<article class="item <?php echo $params->get('classnames_items','');?>" itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
		<meta itemprop="inLanguage" content="<?php echo ($item->language === '*') ? JFactory::getConfig()->get('language') : $item->language; ?>" />
		<?php
			// -- Einleitungsbild
			if($images->image_intro != '' && (bool)$params->get('preview-image',1)):
		?>
				<div class="item-image-intro">
					<img src="<?php echo $images->image_intro;?>" alt="<?php echo $images->image_intro_alt;?>" />
				</div>
		<?php
			endif;
		?>

		<?php
			// -- Protoslider Layout
			echo JLayoutHelper::render('head.protoslider', $item);
		?>
		
		<?php
			// -- Modul-Layout Vorschauvideo
			if($params->get('introvideos',0)):
				require JModuleHelper::getLayoutPath('mod_articles_head', '_itemvideo');
			endif;
		?>

		<?php
			// -- Titel
			if( $params->get('item_title',false) && ( $attribs->get('show_title', false) == 1 || $attribs->get('show_title', false) == '' ) ):
				$htag = $params->get('item_heading','h3');
		?>
				<header itemprop="name" class="item-header">
					<<?php echo $htag;?> class="item-title">
						<?php if( $params->get('link_titles',0) && $item_readmore_url !== '' ):	?><a href="<?php echo $readmore_url;?>"<?php echo $item_readmore_blank ? ' target="_blank"' : '';?>><?php endif; ?>
						<?php echo $item->title;?>
						<?php if( $params->get('link_titles',0) && $item_readmore_url !== '') : ?></a><?php endif; ?>
					</<?php echo $htag;?>>
				</header>
		<?php
				echo $item->afterDisplayTitle;
			endif;
		?>
		<section class="item-introtext" itemprop="articleBody">
			<?php echo $item->beforeDisplayContent;?>

			<?php echo $item->introtext; ?>

			<?php echo $item->afterDisplayContent;?>
		</section>
		<footer class="item-footer">
			<?php
				if($item_readmore_url !== '') :
			?>
					<div class="readmore">
						<a itemprop="url" class="btn btn-primary more" href="<?php echo $item_readmore_url; ?>"<?php echo $item_readmore_blank ? ' target="_blank"' : '';?>>
							<span>
								<?php 
									if ($readmore = $item->alternative_readmore) :
										echo $readmore;
										if ($attribs->get('show_readmore_title', 0) != 0) :
											echo JHtml::_('string.truncate', $item->title, $attribs->get('readmore_limit'));
										endif;
									elseif ($attribs->get('show_readmore_title', 0) == 0) :
										echo JText::sprintf('COM_CONTENT_READ_MORE_TITLE');
									else :
										echo JText::_('COM_CONTENT_READ_MORE');
										echo JHtml::_('string.truncate', $item->title, $attribs->get('readmore_limit'));
									endif;
								?>				
							</span>
							<i></i>
						</a>
					</div>
			<?php
				endif;	
			?>
		</footer>
	</article>
</div>
<span class="item-truncator"></span>