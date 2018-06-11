<?php
/**
 * @package        HEAD. Article Module
 * @version        1.7.2
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


/**

	Weiterlesen-Link für Beiträge

	Folgende Felder, wenn in X-Fields angelegt, stehen zur Verfügung:

	- readmore-override-item 	= Verlinke „Weiterlesen” mit einem Menüeintrag
	- readmore-override-article = Verlinke „Weiterlesen” mit einem anderen Artikel
	- readmore-override-url 	= Verlinke „Weiterlesen” mit einer URL
	- readmore-blank 			= Wird in neuem Fenster geöffnet, wenn TRUE

	Wenn diese Felder nicht existieren wird der normale „Weiterlesen”-Link benutzt. 
 */


$nolink 		= false;
$link_override 	= false;
$link_blank 	= (bool)$attribs->get('readmore-blank', 0);

$readmore_url	= false;

// Link zu Menü-Item
if( $attribs->get('readmore-override-item', 0) )
{
	$link_override = true;

	$menu_item = JFactory::getApplication()->getMenu()->getItem( $attribs->get('readmore-override-item', 0));

	if( $menu_item )
	{
		if( $menu_item->flink )
		{
			$readmore_url = $menu_item->flink;
		}
		else
		{
			$readmore_url = JRoute::_($menu_item->link . '&Itemid=' . $menu_item->id);
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
				$readmore_url = JRoute::_( ContentHelperRoute::getArticleRoute($article->id, $article->catid) );
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

	$readmore_url = $attribs->get('readmore-override-url', '');
}


// Standard-Link
if( !$nolink && !$link_override && $item->fulltext != '' )
{
	$readmore_url = $item->link;
}

?>
