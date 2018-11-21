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
<div class="item-column <?php echo $params->get('classnames_cols','');?>">
	<article class="item <?php echo $params->get('classnames_items','');?>" itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
		<meta itemprop="inLanguage" content="<?php echo ($item->language === '*') ? JFactory::getConfig()->get('language') : $item->language; ?>" />
		<?php
			// -- Einleitungsbild
			if($images->image_intro != '' && (bool)$params->get('preview-image',1)):
		?>
				<div class="item-image-intro">
					<img src="<?php echo $images->image_intro;?>" alt="<?php echo $images->image_intro_alt;?>" />
                    <?php
                        // -- Modul-Layout Vorschauvideo
                        if($params->get('introvideos',0)):
                            require JModuleHelper::getLayoutPath('mod_articles_head', '_itemvideo');
                        endif;
                    ?>
				</div>
		<?php
			endif;
		?>

		<?php
			// -- Protoslider Layout
			echo JLayoutHelper::render('head.protoslider', $item);
		?>

		<?php
			// -- Titel
			if( $params->get('item_title',false) && ( $attribs->get('show_title', false) == 1 || $attribs->get('show_title', false) == '' ) ):
				$htag = $params->get('item_heading','h3');
		?>
				<header itemprop="name" class="item-header">
					<<?php echo $htag;?> class="item-title">
						<?php if( $params->get('link_titles',0) && $item_readmore_url !== '' ):	?><a href="<?php echo $item_readmore_url;?>"<?php echo $item_readmore_blank ? ' target="_blank"' : '';?>><?php endif; ?>
						<?php echo $item->title;?>
						<?php if( $params->get('link_titles',0) && $item_readmore_url !== '') : ?></a><?php endif; ?>
					</<?php echo $htag;?>>
				</header>
		<?php
				echo $item->afterDisplayTitle;
			endif;
		?>
		
		<?php
			// -- Info-Block (Datum, Autor etc.)
			if($params->get('show_infoblock',0)) :
		?>
				<section class="item-infoblock">
					<?php echo JLayoutHelper::render('joomla.content.info_block.block', array('item' => $item, 'params' => $params, 'position' => 'above')); ?>
				</section>
		<?php
			endif;
		?>

		<?php 
			if($item->introtext != ''):
		?>
			<section class="item-introtext" itemprop="articleBody">
				<?php echo $item->beforeDisplayContent;?>

				<?php echo $item->introtext; ?>

				<?php echo $item->afterDisplayContent;?>
			</section>
		<?php
			endif;
		?>
        <?php
            if($params->get('readmore', 0)):
        ?>
            <footer class="item-footer">
                <?php require JModuleHelper::getLayoutPath('mod_articles_head', '_readmore'); ?>
            </footer>
        <?php
            endif;
        ?>
	</article>
</div>
<span class="item-truncator"></span>