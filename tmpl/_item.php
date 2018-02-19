<?php
/**

 2017-06-19
 - Eigenes Template zum generieren des Links für "Weiterlesen" (_itemlink.php) anstelle Layout aus Template.
 
 2016-05-12
 - Titel wird nur noch angezeigt, wenn Einstellung im Modul UND im Artikel auf "Ja" steht.
 
 */
defined('_JEXEC') or die;

// Artikel Parameter und "X-Fields"
$attribs 	= new JRegistry( $item->attribs );

// Bilder
$images 	= json_decode( $item->images );

/* 
	Joomla 3.7 Eigene Felder/Custom Fields
	
	Dazu in den Moduleinstellungen das Triggern der Events einschalten!

	Ausgabe Inhalt:
	
	echo $item->customField['FELDNAME']->value;
*/
JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
foreach($item->jcfields as $field)
{
	$item->jcfields[$field->name] = $field;
}

/*
	Weiterlesen-Link, mit Berücksichtigung von X-Fields Overrides – Gibt auch ohne X-Fields einen Link zurück!

	Deklariert $link
	$link ist entweder ein URL oder false
*/
require JModuleHelper::getLayoutPath('mod_articles_head', '_itemlink');

?>
<div class="item-column">
	<article itemprop="blogPost" itemscope itemtype="https://schema.org/BlogPosting">
		<meta itemprop="inLanguage" content="<?php echo ($item->language === '*') ? JFactory::getConfig()->get('language') : $item->language; ?>" />
		<?php
			// Einleitungsbild
			
			if($images->image_intro != '' && (bool)$params->get('preview-image',1)):
		?>
				<div class="item-image-intro">
					<img src="<?php echo $images->image_intro;?>" alt="<?php echo $images->image_intro_alt;?>" />
				</div>
		<?php
			endif;
		?>
		<?php
			// Titel
			
			if( $params->get('item_title',false) && ( $attribs->get('show_title', false) == 1 || $attribs->get('show_title', false) == '' ) ):
				$htag = $params->get('item_heading','h3');
		?>
				<header itemprop="name">
					<<?php echo $htag;?> class="item-title">
						<?php if( $params->get('link_titles',0) && $link ):	?><a href="<?php echo $link;?>"<?php echo $link_blank ? ' target="_blank"' : '';?>><?php endif; ?>
						<?php echo $item->title;?>
						<?php if( $params->get('link_titles',0) && $link ):	?></a><?php endif; ?>
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
		<footer>
			<?php
				if( $link ):
			?>
					<div class="readmore">
						<a itemprop="url" href="<?php echo $link; ?>"<?php echo $link_blank ? ' target="_blank"' : '';?>>
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