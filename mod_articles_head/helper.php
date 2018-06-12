<?php
/**
 * @package        HEAD. Article Module
 * @version        1.7.3
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

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');

abstract class ModArticlesHeadHelper
{
	/** 
	 * Ermittle die Anzahl der Beiträge. Je nach Moduleintellungen.
	 * 
	 * @param   \Joomla\Registry\Registry  &$params   Ein Objekt, dass die Model-Parameter enthält
	 * 
	 * @return   int  Anzahl der Beiträge
	 * 
	 * @since   1.5
	 */
	public static function getItemsCount(&$params)
	{
		$count = self::getList($params, true);
		return count($count);
	}

	/**
	 * AJAX Controller – Wird von com_ajax aufgerufen und rendert ein Modul partiel.
	 * 
	 * @return   mixed
	 * 
	 * @since   1.5
	 */
	public static function getListAjax()
	{
		$input  = JFactory::getApplication()->input;
		$id 	= $input->get('modid',FALSE,'INT'); // Der Helper weiß sonst nicht, welches Modul gemeint ist – sprich welche Beiträge geladen werden sollen.
		
		if(!$id) return FALSE;

		$dbo = JFactory::getDbo();
		$q	 = $dbo->getQuery(true);

		$q->select('*')
			->from( $dbo->quoteName('#__modules') )
			->where( $dbo->quoteName('id') . ' = ' . $dbo->quote($id) )
			->limit(1);

		$dbo->setQuery( $q );

		if( $dbo->query() )
		{
			$module = $dbo->loadObject();
			$params = new JRegistry($module->params);

			$params->set('ajax', 1);	// Das steuert die Ausgabe im Modul-Template. Das bedeutet, wenn der Parameter gleich 1 ist, wird weniger HTML ausgegeben (siehe tmpl/default.php).
			$params->set('start', $input->get('start',0,'INT')); // Wo das Modul anfängt neue Beiträge zu laden.

			// Das „Module Chrome” (den Modulstil aus den Moduleinstellungen, das ist z.B. 'html5' oder 'xhtml' etc.) an dieser Stelle überschreiben, weil wir nur einen Teil der Ausgabe brauchen.
			$chrome = "none";
			$params->set('style', $chrome);

			// Die Paramerter zurückschreiben ...
			$module->params = $params->toString();

			// ... und den Spaß wieder an Joomla übergeben:
			return JModuleHelper::renderModule($module);
		}

		return FALSE;
	}

	/**
	 * Hole die Artikelliste vom Artikel-Model
	 * 
	 * @param   \Joomla\Registry\Registry  &$params  object holding the models parameters
	 * @param 	bool  $count  Ein boolescher Wert der die Nachbearbeitung der erhaltenen Beiträge verhindert (Events etc.) – aus Performancegründen für AJAX Interface und self::getItemsCount
	 *
	 * @return  mixed
	 *
	 * @since 1.5
	 */
	public static function getList(&$params, $count = false)
	{
		// Get an instance of the generic articles model
		$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app	   = JFactory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		// Cru. Start dynamisch gemacht (siehe ajax_loadmodule.php im Template-Ordner):
		// $model->setState('list.start', 0);
		$model->setState('list.start', (int) $params->get('start', 0));
		if( $params->get('limit',0) > 0 )
		{
			$model->setState('list.limit', (int) $params->get('limit', 5));
		}
		else if( $params->get('count', 0) > 0 )
		{
			$model->setState('list.limit', (int) $params->get('count', 5));
		}
		else
		{
			$model->setState('list.limit', 4000000000 ); // 4 Mrd. entspricht – nach meiner Kenntniss – dem Maximum in einer MySQL Tabelle, zumindest bei dem alten MYISAM
		}
		$model->setState('filter.published', 1);

		// Access filter
		$access	 = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		$model->setState('filter.category_id', $params->get('catid', array()));

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());

		// Featured switch
		switch ($params->get('show_featured'))
		{
			case '1' :
				$model->setState('filter.featured', 'only');
				break;
			case '0' :
				$model->setState('filter.featured', 'hide');
				break;
			default :
				$model->setState('filter.featured', 'show');
				break;
		}

		// Set ordering
		$ordering = $params->get('ordering', 'a.publish_up');
		$model->setState('list.ordering', $ordering);

		if (trim($ordering) === 'rand()')
		{
			$model->setState('list.ordering', JFactory::getDbo()->getQuery(true)->Rand());
		}
		else
		{
			$direction = $params->get('direction', 1) ? 'DESC' : 'ASC';
			$model->setState('list.direction', $direction);
			$model->setState('list.ordering', $ordering);
		}

		/**
			CRu.: Filer by tag aus mod_articles_news
		*/
		$model->setState('filter.tag', $params->get('tag'), array());

		// Retrieve Content
		$items = $model->getItems();

		/**
			CRu.:
			Wenn das Modul in option=com_content&view=article (einem Beitrag) angezeigt wird, und in den Optionen „Aktuellen Artikel vebergen” eingeschaltet ist, und das Modul eine Kategorie zeigt, 
			in welcher der Beitrag, der gerade angezeigt wird, vorhanden ist, wird dieser Beitrag hier rausgeschmissen und erscheint nicht im Modul:
		*/
		if($params->get('hidecurrentarticle',0))
		{
			$env = $app->input->getArray(array('option' => 'string', 'view' => 'string', 'id' => '', 'Itemid' => 'int'));
			$env["id"] = explode(":", $env["id"]); // Fix Artikel Slug

			foreach($items as $i => $item) {
				if( $env["option"] == 'com_content' && $env["view"] == 'article' && $item->id == $env['id'][0] )
				{
					array_splice($items, $i, 1);
					break;
				}
			}
		}

		/**
			CRu.:
			Wir benötigen nur die Anzahl der Items und brechen an dieser Stelle ab.
		*/
		if($count) {
			return $items;
		}

		/**
			CRu./TSö.: 
			Check if we should trigger additional plugin events – aus mod_articles_news
		*/
		$triggerEvents = $params->get('triggerevents', 1);

		foreach ($items as $idx => &$item)
		{
			$item->readmore = strlen(trim($item->fulltext));
			$item->slug	 = $item->id . ':' . $item->alias;

			/** @deprecated Catslug is deprecated, use catid instead. 4.0 **/
			$item->catslug  = $item->catid . ':' . $item->category_alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link	 = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
				$item->linkText = JText::_('MOD_ARTICLES_NEWS_READMORE');
			}
			else
			{
				$item->link = new JUri(JRoute::_('index.php?option=com_users&view=login', false));
				$item->link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)));
				$item->linkText = JText::_('MOD_ARTICLES_NEWS_READMORE_REGISTER');
			}

			$item->introtext = JHtml::_('content.prepare', $item->introtext, '', 'mod_articles_news.content');

			if (!$params->get('image'))
			{
				$item->introtext = preg_replace('/<img[^>]*>/', '', $item->introtext);
			}

			/**
				CRu./TSö.: 
				Check if we should trigger additional plugin events – aus mod_articles_news
			*/
			if ($triggerEvents)
			{
				$item->text = '';
				$app->triggerEvent('onContentPrepare', array ('com_content.article', &$item, &$params, 0));

				$results				 = $app->triggerEvent('onContentAfterTitle', array('com_content.article', &$item, &$params, 0));
				$item->afterDisplayTitle = trim(implode("\n", $results));

				$results					= $app->triggerEvent('onContentBeforeDisplay', array('com_content.article', &$item, &$params, 0));
				$item->beforeDisplayContent = trim(implode("\n", $results));

				$results				   = $app->triggerEvent('onContentAfterDisplay', array('com_content.article', &$item, &$params, 0));
				$item->afterDisplayContent = trim(implode("\n", $results));
			}
			else
			{
				$item->afterDisplayTitle	= '';
				$item->beforeDisplayContent = '';
				$item->afterDisplayContent  = '';
			}
		}

		return $items;
	}
}
