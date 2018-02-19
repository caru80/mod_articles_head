<?php
/**
	
	Weiterlesen-Link
	
	Mit Berücksichtigung der Overrides aus X-Fields.
	
 */
defined('_JEXEC') or die;

$nolink 		= false;
$link_override 	= false;
$link			= false;
$link_blank 	= $attribs->get('readmore-blank', 0);

// Link zu Menü-Item
if( $attribs->get('readmore-override-item', 0) )
{
	$link_override = true;

	$menu_item = JFactory::getApplication()->getMenu()->getItem( $attribs->get('readmore-override-item', 0));
	
	if( $menu_item )
	{
		if( $menu_item->flink )
		{
			$link = $menu_item->flink;
		}
		else
		{
			$link = JRoute::_($menu_item->link . '&Itemid=' . $menu_item->id);			
		}
	}
	else{
		$nolink = true;
	}
}

// Link zu anderem Artikel
if( $attribs->get('readmore-override-article', 0) )
{
	$artid = $attribs->get('readmore-override-article', 0);

	$db = JFactory::getDbo();
	$q 	= $db->getQuery(true);
	
	$q->select($db->quoteName('id') .', '. $db->quoteName('state') )->from($db->quoteName('#__content'))->where($db->quoteName('id') . ' = ' . $db->quote($artid));
	
	$db->setQuery($q);
	$db->execute();

	if( $result = $db->loadObject() ) // Artikel ist da...
	{
		if( $result->state == '1' ) // ...und ist veröffentlicht
		{
			$link_override = true;
	
			// Hole Artikel Model, weil es nicht immer verfügbar ist, und hole damit den Artikel.
			JModelLegacy::addIncludePath(JPATH_SITE . DIRECTORY_SEPARATOR .'components' . DIRECTORY_SEPARATOR. 'com_content' . DIRECTORY_SEPARATOR . 'models', 'ContentModel');
			$articleModel = JModelLegacy::getInstance('Article','ContentModel');
			
			$article = $articleModel->getItem( $artid );
			
			if( $article )
			{
				$link = JRoute::_( ContentHelperRoute::getArticleRoute($article->id, $article->catid) );
			}
		}
	}

	if( !$link_override ){
		$nolink = true;
	}
}

// Link zu URL
if( $attribs->get('readmore-override-url', '') )
{
	$link_override = true;
	
	$link = $attribs->get('readmore-override-url', '');
}


// Standard Verlinkung
if( !$nolink && !$link_override && $item->fulltext != '' )
{
	$link = $item->link;
}

?>