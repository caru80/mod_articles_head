<?php
/**
 * @package        HEAD. Article Module
 * @version        1.10.0
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

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Categories\Categories;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Helper\TagsHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Access\Access;
use Joomla\CMS\HTML\HTMLHelper;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');


abstract class ModArticlesHeadHelper
{
	/** 
	 * Ermittle die Anzahl der Beiträge. Je nach Moduleintellungen.
	 * 
	 * @param   \Joomla\Registry\Registry  &$params   Ein Objekt, dass die Model-Parameter enthält
	 * @param   Boolean  $all   Hole die Anzahl aller Beiträge, die mit den aktuellen Filtereinstellungen angezeigt werden.
	 * 
	 * @return   int  Anzahl der Beiträge
	 * 
	 * @since   1.5
	 */
	public static function getItemsCount(&$params, $all = false)
	{
		if($all) {
			$p = clone $params;
			$p->set('start', 0);
			$p->set('count', 0);
			$count = self::getList($p, true);
			unset($p);
		}
		else {
			$count = self::getList($params, true);
		}
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
		$input  = Factory::getApplication()->input;
		$id 	= $input->get('modid',FALSE,'INT'); // Der Helper weiß sonst nicht, welches Modul gemeint ist – sprich welche Beiträge geladen werden sollen.
		
		if(!$id) return FALSE;

		$dbo = Factory::getDbo();
		$q	 = $dbo->getQuery(true);
		
		$q->select('*')
			->from( $dbo->quoteName('#__modules') )
			->where( $dbo->quoteName('id') . ' = ' . $dbo->quote($id) )
			->limit(1);

		$dbo->setQuery( $q );

		if( $module = $dbo->loadObject() )
		{
			/*
				Der Trick ist, dass wir hier das Modulobjekt aus der Datenbank holen, dessen Parameter (z.B.: Kategorien und Schlagworte) lokal überschreiben, das Modul rendern, und über com_ajax an das JavaScript zurückgeben. 
			*/
			$params = new Registry($module->params);

			$params->set('ajax', 1);	// Das steuert die Ausgabe im Modul-Template. Das bedeutet, wenn der Parameter gleich 1 ist, wird weniger HTML ausgegeben (siehe tmpl/default.php).
			$params->set('start', $input->get('start',0,'INT')); // Wo das Modul anfängt neue Beiträge zu laden.

			// Kategorie-Filter
			if($input->get('catid',FALSE,'INT')) 
			{
				$params->set('catid', $input->get('catid')); // Das wird in die Modulparameter geschrieben, damit das Modul nur das anzeigt, was es soll.
				$params->set('filter_catid', $input->get('catid')); // Das wird für den Button Loadmore benötigt damit sich dessen Konfiguration entsprechend anpasst, wenn gefiltert wird.
			}

			// Schlagworte-Filter
			if($input->get('tag',FALSE,'INT')) 
			{
				$params->set('tag', $input->get('tag'));
				$params->set('filter_tag', $input->get('tag'));
			}

			// Custom Fields
			$custom = $input->post->get('custom', '', 'raw');
			if($custom !== '') 
			{
				$params->set('filter_custom', $custom);
			}

			// Das „Module Chrome” (den Modulstil aus den Moduleinstellungen, das ist z.B. 'html5' oder 'xhtml' etc.) an dieser Stelle überschreiben, weil wir nur einen Teil der Ausgabe brauchen.
			$params->set('style', 'none');

			// Die Paramerter zurückschreiben ...
			$module->params = $params->toString();

			// ... und den Spaß wieder an Joomla übergeben:
			return ModuleHelper::renderModule($module);
		}

		return FALSE;
	}

	/**
	 * AJAX - Konfiguration für den Hyperlink „Mehr-laden” .
	 * 
	 * @param   stdClass  &$module   Ein Objekt, welches das Modul repräsentiert.
	 * 
	 * @return  stdClass  Ein Objekt, welches die Konfiguration für den AJAX-Request enthält.
	 * 
	 * @since   1.7.4
	 * 
	 * Das hier produzierte Objekt wird im Frontend für den Link „Mehr Laden”, die Paginierung und die Filter benutzt.
	 * 
	 */
	public static function getAjaxLinkConfig(&$module) {

		$params = new Registry($module->params);

		// -- AJAX-Request Grundkonfiguration
		$config = (object) [
			'url' 		=> Uri::root() . 'index.php', 					// Basis-URL, das JavaScript fügt alles weitere ein.
			'id'		=> $module->id, 										// Die Id des Moduls, das diese Funktion aufruft.
			's' 		=> $params->get('start',0) + $params->get('count', 4), 	// Start...
            'target' 	=> '#mod-intro-items-list-' . $module->id, 				// Ziel zum einhängen des neuen Contents
            'replace' 	=> ((int)$params->get('ajax_loadmore_type', 1) === 1 
							&& (int)$params->get('ajax_replace_content', 0) === 1 ? true : false)
		];
		
		if($params->get('filter_catid',false,'INT')) // Es wird gerade nach Kategorie gefiltert, für den Link „Mehr Laden”.
		{
			$config->catid = $params->get('filter_catid');
		}

		if($params->get('filter_tag',false,'INT')) // Es wird gerade nach Tags gefiltert, für den Link „Mehr Laden”.
		{
			$config->tag = $params->get('filter_tag');
		}

		if($params->get('filter_custom', false)) // Es wird nach Werten aus Eigenen Feldern gefiltert.
		{
			$config->custom = $params->get('filter_custom');
		}

		// -- Post-Animationen
		if($params->get('ajax_post_animations',0)) 
		{
			$config->animate 	= true;
			$config->aniclass 	= $params->get('ajax_post_animation_class','');
			$config->aniname 	= $params->get('ajax_post_animation_name','');
		}

		return $config;
	}

	public static function getCustomFilterArticles($fieldid, $values, $type = 'default')
	{
		$dbo = CMS\Factory::getDbo();
		$query = $dbo->getQuery(true);

		switch($type)
		{
			case 'range' :
			case 'html5range' :
				$query->select('DISTINCT '. $dbo->quoteName('item_id'))
					->from($dbo->quoteName('#__fields_values'))
					->where($dbo->quoteName('field_id') .'='. $fieldid . ' AND ' . $dbo->quoteName('value') . ' >= ' . $values[0] . ' AND ' . $dbo->quoteName('value') . ' <= ' . $values[1] );
			break;

			default : 
				$query->select('DISTINCT '. $dbo->quoteName('item_id'))
					->from($dbo->quoteName('#__fields_values'))
					->where($dbo->quoteName('field_id') .'='. $fieldid . ' AND ' . $dbo->quoteName('value') . ' IN (\'' . implode('\',\'', $values) . '\')');
		}
		$dbo->setQuery($query);

		return $dbo->loadColumn();
	}


	public static function getCustomFilterType(&$params, $id)
	{
		$filters = $params->get('ajax_filter', array());
		foreach($filters as $i => $filter)
		{
			if((int)$filter->filter_field === (int)$id)
			{
				return $filter->template;
			}
		}
		return 'default';
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
		$model = BaseDatabaseModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app	   = Factory::getApplication();
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
		$access	 = !ComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Category filter
		$model->setState('filter.category_id', $params->get('catid', array()));

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());

        // Featured switch
		$featured = $params->get('show_featured', '');
		if ($featured === '')
		{
			$model->setState('filter.featured', 'show');
		}
		elseif ($featured)
		{
			$model->setState('filter.featured', 'only');
		}
		else
		{
			$model->setState('filter.featured', 'hide');
		}

		// Set ordering
		$ordering = $params->get('ordering', 'a.publish_up');
		$model->setState('list.ordering', $ordering);

		if (trim($ordering) === 'rand()')
		{
			$model->setState('list.ordering', Factory::getDbo()->getQuery(true)->Rand());
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

		/*
			CRu.: Custom Fields Filter
		*/
		if($params->get('filter_custom',false))
		{
			$count = 0;
			$filterArticles = array();
			$tmpArticles = array();
			foreach($params->get('filter_custom') as $id => $values)
			{
				if(count($values))
				{
					$tmpArticles = self::getCustomFilterArticles($id, $values, self::getCustomFilterType($params, $id));
				}

				if($count == 0)
				{
					$filterArticles = $tmpArticles;
				}
				else
				{
					$filterArticles = array_intersect($filterArticles, $tmpArticles); // Nur Ids übernehmen, die in beiden Arrays vorkommen.
				}
				$count++;
			}
			if($count > 0)
			{
				if(count($filterArticles))
				{
					$model->setState('filter.article_id', $filterArticles);
					$model->setState('filter.article_id.include', true);
				}
				else
				{
					$model->setState('filter.article_id', array(0));
					$model->setState('filter.article_id.include', true);
				}
			}
		}

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
				$item->link	 = Route::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
				$item->linkText = Text::_('MOD_ARTICLES_NEWS_READMORE');
			}
			else
			{
				$item->link = new Uri(Route::_('index.php?option=com_users&view=login', false));
				$item->link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language)));
				$item->linkText = Text::_('MOD_ARTICLES_NEWS_READMORE_REGISTER');
			}

			$item->introtext = HTMLHelper::_('content.prepare', $item->introtext, '', 'mod_articles_news.content');

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


	/**
	 * Generiert den Modul-Weiterlesen-Link
	 * 
	 * @param   \Joomla\Registry\Registry  &$params   Ein Registry Objekt, das die Modulparameter enthält.
	 * 
	 * @return   string   Ein String, welcher den Modul-Weiterlesen-Link repräsentiert oder ein leerer String, wenn kein URL generiert werden konnte.
	 * 
	 * @since 1.10.0
	*/
	public static function getModuleReadmoreUrl(&$params)
	{
		$type 	= $params->get('module_readmore_type', 0);
		$app 	= Factory::getApplication();
		$url  	= '';

		switch($type) 
		{
			case 'url' :
				$url = $params->get('module_readmore_url', '');
			break;

			case 'category' :
				$catids = $params->get('catid', array());
				$url 	= Route::_( ContentHelperRoute::getCategoryRoute(reset($catids)) );
			break;

			case 'menuitem' :
				$item = $params->get('module_readmore_menuitem', '');

				if ($item != '')
				{
					$item = $app->getMenu()->getItem($item);

					if ($item->type === 'alias')
					{
						$item_params = new Registry($item->params);
						$item = $app->getMenu()->getItem($item_params->get('aliasoptions', null));
						unset($item_params);
					}

					switch($item->type)
					{
						case 'component' :
							$url = Route::_($item->link . '&Itemid=' . $item->id);
						break;

						case 'url' :
							$url = $item->link;
						break;
					}
				}
			break;
	
		}
		return $url;
	}


	/** 
	 * Generiert den Weiterlesen-Link eines Beitrags
	 * 
	 * @param   stdClass  &$item   Ein Objekt, welches ein Beitrags-Model repräsentiert.
	 * 
	 * @return   string   Ein String, welcher den Weiterlesen-URL repräsentiert oder ein leerer String, wenn gar kein URL ermittelt werden konnte.
	 * 
	 * @since   1.7.4
	 */
	public static function getReadmoreUrl(&$item) 
	{
		$attribs 		= new Registry($item->attribs);
		$readmore_url 	= '';

		switch($attribs->get('xfields_readmore_override',''))
		{
			case 'menuitem' :
				$menu_item = Factory::getApplication()->getMenu()->getItem($attribs->get('xfields_readmore_override_menuitem', 0));

				if($menu_item)
				{
					if($menu_item->flink)
					{
						$readmore_url = $menu_item->flink;
					}
					else
					{
						$readmore_url = Route::_($menu_item->link . '&Itemid=' . $menu_item->id);
					}
				}
			break;

			case 'article' : 
				$id = $attribs->get('xfields_readmore_override_article', 0);

				$db = Factory::getDbo();
				$q 	= $db->getQuery(true);
	
				$q->select($db->quoteName('id') .', '. $db->quoteName('state'))
					->from($db->quoteName('#__content'))
					->where($db->quoteName('id') . ' = ' . $db->quote($id));
	
				$db->setQuery($q);
				//$db->execute();
	
				if($result = $db->loadObject()) // Artikel ist vorhanden.
				{
					if($result->state !== '1') break; // Der Artikel ist nicht veröffentlicht.

					// -- Instanziere ein Artikel Model, weil es nicht immer verfügbar ist, und hole damit den Artikel.
					$model = BaseDatabaseModel::getInstance('Article','ContentModel');

					$article = $model->getItem( $id );

					if( $article )
					{
						$readmore_url = Route::_(ContentHelperRoute::getArticleRoute($article->id, $article->catid));
					}
				}
			break;

			case 'url' :
				$readmore_url = $attribs->get('xfields_readmore_override_url', '');
			break;

			default : 
				// -- Standard-URL, wenn ein Fulltext vorhanden ist.
				if($item->fulltext) 
				{
					$readmore_url = $item->link;
				}
		}

		return $readmore_url;
	}
	
	
	/** 
	 * Stellt die Konfiguration für einen Filter zusammen, und gibt diese als Objekt zurück.
	 * 
	 * @param   object  &$module   Ein Objekt, welches das Modul-Model repräsentiert.
	 * @param   object  $filterParams   Ein Objekt, das die Filterkonfiguration aus den Moduleinstellungen enthält.
	 * 
	 * @return   object  Ein Objekt, dass die Filterkonfiguration zum aufbauen der Auswahl im Frontend enthält.
	 * 
	 * @since   1.8.0
	 */
	public static function getFilter(&$module, $filterParams) 
	{
		$params = new Registry($module->params);

		$filter 			= new stdClass();
		$filter->type       = $filterParams->filter_type;
		$filter->label      = $filterParams->filter_label;
		$filter->multiple 	= $filterParams->multiple;
		$filter->template 	= $filterParams->template;
		$filter->show_items = $filterParams->show_items_count;
		$filter->options    = array();	// Frontend Auswahl
		$filter->param_name = null; // Wenn sich der Filter auf einen Modul-Parameter bezieht, wird der hier gespeichert. Sonst bleibt das null.

		switch($filter->type)
		{
			case "category" :
				$filter->param_name = 'catid';

				$options 	= $params->get($filter->param_name, array());
				$model 		= Categories::getInstance('content');
			break;

			case "tag" :
				$filter->param_name = 'tag';

				$options 	= $params->get($filter->param_name, array());
				$model 		= new TagsHelper();
			break;

			case "custom" :
				$filter->param_name 	= 'custom';
				$filter->filter_field 	= $filterParams->filter_field;

				$options = array();

				// Ist dieses Feld in den im Modul gewählten Kategorien vorhanden?
				$dbo 	= CMS\Factory::getDbo();
				$query 	= $dbo->getQuery(true);

				$query->select($dbo->quoteName('category_id'))
					->from($dbo->quoteName('#__fields_categories'))
					->where($dbo->quoteName('field_id') . '=' . $filter->filter_field . ' OR ' . $dbo->quoteName('category_id') . "=''");

				$dbo->setQuery($query);

				$intersection = array_intersect($params->get('catid'), $dbo->loadColumn());

				if(count($intersection) > 0) // Das Feld ist vorhanden; Werte holen.
				{
					$query->clear();
					/*$query->select('DISTINCT ' . $dbo->quoteName('value'))
						->from('#__fields_values')
						->where($dbo->quoteName('field_id') . '=' . $filter->filter_field)
						//->order('CAST (' . $dbo->quoteName('value') . ' AS DECIMAL(2,2)) ASC');
						->order('CAST (value AS DECIMAL(2,2)) ASC');*/

					$query->select('DISTINCT ' . $dbo->quoteName('value'))
						->from('#__fields_values')
						->where($dbo->quoteName('field_id') . '=' . $filter->filter_field);

					switch($filter->template)
					{
						case 'range' :
						case 'html5range' : 
							$query->order(' CAST(' . $dbo->quoteName('value') . ' AS DECIMAL(12,2)) ASC');
						break;

						default : 
							$query->order($dbo->quote('value') . ' ASC');
					}

					//$query = "SELECT DISTINCT value FROM #__fields_values WHERE field_id=" . $filter->filter_field ." ORDER BY CAST(value AS DECIMAL(12,2)) ASC";
					$options = $dbo->setQuery($query)->loadColumn();
				}
			break;
		}
		
		/*
		$filter->field_name = $filter->param_name . ($filter->multiple ? '[]' : ''); // Für das HTML Feld etc.
		$filter->group_data = "data-filtergroup='" . json_encode((object) array("name" => $filter->param_name, "field" => $filter->field_name)) . "'";
		*/
		$filter->field_name = $filter->param_name;
		if($filter->type === 'custom')
		{
			$filter->field_name .= '-' . $filter->filter_field;
		}

		if($filter->multiple 
			|| $filter->template === 'range'
			|| $filter->template === 'html5range') 
		{
			$filter->field_name .= '[]';
		}
		
		$filter->group_data = array(
			"param" => $filter->param_name, 
			"type" 	=> $filter->template,
			"field" => $filter->field_name,
			"id" 	=> !empty($filter->filter_field) ? $filter->filter_field : ''
		);		
		$filter->group_data = "data-filtergroup='" . json_encode((object) $filter->group_data) . "'";

		$ajaxConfig 			= self::getAjaxLinkConfig($module);
		$ajaxConfig->filter 	= true;	// Dem AJAX-Script mitteilen, dass ein Filter angeklicht wurde (sonst wird der Filter beim Anklicken entfernt)
		$ajaxConfig->s 			= 0;	// Start (ab dem 1. Beitrag)
		$ajaxConfig->replace 	= true; // Inhalt im Modul ersetzen
		
		$filter->reset_option = new stdClass();
		$filter->reset_option->ajax_config = $ajaxConfig;
		if($filter->type === 'custom')
		{
			$filter->reset_option->ajax_config->{$filter->param_name} = array($filter->filter_field => array(''));
		}
		else
		{
			$filter->reset_option->ajax_config->{$filter->param_name} = array();
		}
		$filter->reset_option->ajax_json = json_encode($filter->reset_option->ajax_config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

		foreach($options as $opt) 
		{
			$filterOption = new stdClass();
			$filterOption->raw_value 	= $opt;
			$filterOption->ajax_config 	= $ajaxConfig;

			switch($filter->type)
			{
				case 'category' :
					$filterOption->title = $model->get($opt)->title;
					$filterOption->ajax_config->{$filter->param_name} = array($opt);
				break;

				case 'tag' :
					$filterOption->title = $model->getTagNames(array($opt))[0];
					$filterOption->ajax_config->{$filter->param_name} = array($opt);
				break;

				case 'custom' :
					$filterOption->title = $opt;
					$filterOption->ajax_config->{$filter->param_name}[$filter->filter_field] = array($opt);
				break;

				default :
					$filterOption->title = $opt;
			}

			$filterOption->ajax_json = json_encode($filterOption->ajax_config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

			if($filterParams->show_items_count) 
			{
				// Hier braucht es noch eine Weiche für Custom Fields; -> Update: Oder auch nicht.
				$tempParams = clone $params;
				$tempParams->set('start', 0);
				$tempParams->set('count', 0);
				$tempParams->set($filter->param_name, $opt);

				$filterOption->items_count = count(self::getList($tempParams, true));
			}

			// $filter->options[$filterOption->title] = $filterOption;
			$filter->options[] = $filterOption;
		}

		if($filterParams->filter_sorting === 'asc')
		{
			ksort($filter->options);
		}
		else{
			krsort($filter->options);
		}

		return $filter;
	}

	/** 
	 * Generiert die Liste für die Paginierung
	 * 
	 * @param   stdClass  &$module   Ein Objekt, welches das Modul-Model repräsentiert
	 * 
	 * @return   array  Ein Array, das die Liste für die Paginierung enthält.
	 * 
	 * @since   1.8.0
	 */
	public static function getPaginationList(&$module) 
	{
		$params = new Registry($module->params);

		$pages = new stdClass();
		$pages->items 	= (int) self::getItemsCount($params, true); 	// Anzahl Beiträge
		$pages->limit 	= (int) $params->get('count', 0);				// Anzahl Beiträge auf einer Seite
		$pages->total 	= (int) ceil($pages->items / $pages->limit);	// Gesamtseitenzahl
		$pages->current = (int) ceil(($params->get('start') - $pages->limit + 1) / $pages->limit + 1); // Aktuelle Seite

		$list = array(
			"start",
			"previous",
			"next",
			"end"
		);

		// Pagination-Layout, wie in Moduloptionen angegeben.
		$layout = $params->get('ajax_pagination_layout', array());
	
		foreach($list as $key => $name) 
		{
			$config = self::getAjaxLinkConfig($module);
			$config->replace = true;
				
			switch($name) 
			{
				case "start" :
					if($config->s - $pages->limit == 0) 
					{
						$config->s = null;
					}
					else {
						$config->s = 0;
					}
				break;
	
				case "previous" :
					if($config->s - ($pages->limit * 2) < 0) 
					{
						$config->s = null;
					}
					else 
					{
						$config->s = $config->s - ($pages->limit * 2);
						$config->s = $config->s <= 0 ? 0 : $config->s;
					}
				break;
	
				case "next" :
					if($config->s >= $pages->items) 
					{
						$config->s = null; 
					}
				break;
	
				case "end" :
					if($pages->total === $pages->current || $pages->total === 0)
					{
						$config->s = null;
					}
					else 
					{
						$config->s = $pages->limit * ($pages->total - 1);
					}
				break;
			}

			$list[$name] = new stdClass();
			$list[$name]->show      = in_array($name, $layout);
			$list[$name]->config 	= $config->s === null ? "" : "data-modintroajax='" . json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK) . "'";
			$list[$name]->disabled 	= $config->s === null ? true : false;
			$list[$name]->text 		= Text::_("MODARH_PAGINATION_" . strtoupper($name) . "_LABEL");
		}
	
		$list["pages"] 			= new stdClass();
		$list["pages"]->options = array();
		$list["pages"]->show 	= in_array("pages", $layout);
		$list["pages"]->total 	= $pages->total;
		$list["pages"]->current = $pages->current;

		//
		// Seitenzahlen – Ranged
		//
		$range 		= 1;
		$step  		= (int)$params->get('ajax_pagination_range',4);
		$current 	= $pages->current - 1; // $pages->current beginnt ab 1, gerechnet wird aber ab 0

		if ($current >= $step)
		{
			if ($current % $step == 0)
			{
				$range = ceil($current / $step) + 1;
			}
			else
			{
				$range = ceil($current / $step);
			}
		}

		for($i = 0; $i < $pages->total; $i++) 
		{
			if(in_array($i, range($range * $step - ($step + 1), $range * $step))) // Der Seitenlink mit dem Wert von $i darf in der Paginierung erscheinen
			{
				$list["pages"]->options[$i] = new stdClass();

				if($i + 1 !== $pages->current)
				{
					$config->s = $pages->limit * $i;
					$list["pages"]->options[$i]->current = false;
				}
				else 
				{
					$config->s = null;
					$list["pages"]->options[$i]->current = true;
				}

				$list["pages"]->options[$i]->config     = $config->s === null ? "" : "data-modintroajax='" . json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK) . "'";
				$list["pages"]->options[$i]->disabled 	= $config->s === null ? true : false;
				$list["pages"]->options[$i]->text 		= $i + 1;

				if (($i % $step == 0 || $i == $range * $step - ($step + 1)) && $i != $current && $i != $range * $step - $step)
				{
					$list["pages"]->options[$i]->range = true; // Für das Frontend. Sagt aus, dass es sich um einen „Range-Link” handelt, der im Frontend durch „...” dargestellt wird.
				}
			}
		}
		
		// Array indizes zurücsetzen – von 0 nach n (optional, sonst beginnt der Index bei der Ersten Seite, die in der Paginierung ercheinen darf)
		$list["pages"]->options = array_values($list["pages"]->options);

		return $list;
	}
}
