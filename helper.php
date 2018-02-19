<?php
/**

	HEAD. Artikelmodul 1.0.2
	Cru. 2017-07-05


	2017-07-05
	+ Fix Kompatibilität mit Joomla 3.7.3

 */

defined('_JEXEC') or die;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');

/**
 * Helper for mod_articles_news
 *
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @since       1.6
 */
abstract class ModArticlesHeadHelper
{
	/**
	 * Get a list of the latest articles from the article model
	 *
	 * @param   \Joomla\Registry\Registry  &$params  object holding the models parameters
	 *
	 * @return  mixed
	 *
	 * @since 1.6
	 */
	public static function getList(&$params)
	{
		// Get an instance of the generic articles model
		$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app       = JFactory::getApplication();
		$appParams = $app->getParams();
		$model->setState('params', $appParams);

		// Set the filters based on the module params
		$model->setState('list.start', 0);
		$model->setState('list.limit', (int) $params->get('count', 5));
		$model->setState('filter.published', 1);

		// Access filter
		$access     = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		$model->setState('filter.category_id', $params->get('catid', array()));

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());

		//  Featured switch
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

		// Check if we should trigger additional plugin events
		// CRu./TSö. Joomla 3.7 – Auslösen von Content Events kann nun in den Moduleinstellungen ein-/ausgeschaltet werden
		$triggerEvents = $params->get('triggerevents', 1);

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
			$model->setState('list.limit', 4000000000 );
		}

		// Retrieve Content
		$items = $model->getItems();


		if( $params->get('hidecurrentarticle',0) )
		{
			// CRu.: Derzeit angezeigten Artikel aus Liste ausschließen, wenn com_content article
			$jinput = $app->input;
			$env = array( 'option' => '', 'view' => '', 'id' => '', 'Itemid' => '' );
			$env = $jinput->getArray($env);

			// Fix Artikel Slug
			$env["id"] = explode(":", $env["id"]);
		}


		foreach ($items as $idx => &$item)
		{
			// CRu.: Derzeit angezeigten Artikel aus Liste ausschließen:

			if( $params->get('hidecurrentarticle',0) )
			{
				if( $env["option"] == 'com_content' && $env["view"] == 'article' && $item->id == $env['id'][0] )
				{
					array_splice($items, $idx, 1);
					continue;
				}
			}

			$item->readmore = strlen(trim($item->fulltext));
			$item->slug     = $item->id . ':' . $item->alias;

			/** @deprecated Catslug is deprecated, use catid instead. 4.0 **/
			$item->catslug  = $item->catid . ':' . $item->category_alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link     = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
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

			if ($triggerEvents)
			{
				$item->text = '';
				$app->triggerEvent('onContentPrepare', array ('com_content.article', &$item, &$params, 0));

				$results                 = $app->triggerEvent('onContentAfterTitle', array('com_content.article', &$item, &$params, 0));
				$item->afterDisplayTitle = trim(implode("\n", $results));

				$results                    = $app->triggerEvent('onContentBeforeDisplay', array('com_content.article', &$item, &$params, 0));
				$item->beforeDisplayContent = trim(implode("\n", $results));

				$results                   = $app->triggerEvent('onContentAfterDisplay', array('com_content.article', &$item, &$params, 0));
				$item->afterDisplayContent = trim(implode("\n", $results));
			}
			else
			{
				$item->afterDisplayTitle    = '';
				$item->beforeDisplayContent = '';
				$item->afterDisplayContent  = '';
			}
		}

		return $items;
	}

	// Cru, nur Anzahl Items ausgeben für AJAX
	public static function getItemsCount(&$params)
	{
		$count = self::getList($params);
		return count($count);
	}
}
