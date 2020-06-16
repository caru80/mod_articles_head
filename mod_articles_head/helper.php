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
use Joomla\CMS\Object\CMSObject;
use Joomla\CMS\Filesystem\File;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');
BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_content/models', 'ContentModel');


class ModArticlesHeadHelper
{	
	public $filterList;

	private $module;
	private $params;

	protected $__state_set = null;
	protected $option;
	protected $state;

	function __construct(&$module) 
	{
		$this->module 		= $module;
		$this->params 		= new Registry($module->params);
		$this->option 		= 'modintro' . $module->id;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation.
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	protected function populateState ()
	{
		$app 		= Factory::getApplication();
		$storage 	= $app->getUserState($this->option);

		$this->state = new CMSObject();

		$this->setState('start', $this->params->get('start', 0));
		$this->setState('limit', $this->params->get('limit', 6));

		$active_filters = array();

		// Setze den Kategoriefilter
		$default_category = $this->getFilterDefaultValue('category');
		if ($default_category !== '')
		{
			$active_filters[] = 'catid';
			$this->setState('catid', $default_category);
		}
		else 
		{
			$this->setState('catid', $this->params->get('catid', array()));
		}

		// Setze den Schlagwortfilter
		$default_tag = $this->getFilterDefaultValue('tag');
		if ($default_tag !== '')
		{
			$active_filters[] = 'tag';
			$this->setState('tag', $default_tag);
		}
		else 
		{
			$this->setState('tag', $this->params->get('tag', array()));
		}

		// Eigene Felder
		$this->setState('custom', array());

		// Derzeit aktive Frontend-Filter
		$this->setState('active_filters', $active_filters);


		// Das Flag $storage->xhr sagt aus, dass zuvor eine Config per XHR in den Sitzungspeicher geschrieben wurde, und diese nun zur Ausgabe verwendet werden soll.
		// Ist es nicht XHR und die Verwendung des Sitzungsspeichers ist in den Params aktiviert, wird auch die Config aus der Sitzung benutzt, wenn sie existiert.
		if ( $storage && $storage->xhr
			|| $storage && $this->params->get('ajax_userstate', 0) && $this->params->get('ajax_loadmore_type', 0) != 1)
		{
			/*
				Default-Wert per getState abzuholen erzeugt eine Endlosschleife, weil getState beim ersten Aufruf populateState auswführt, und das solange tut, bis populateState einmal durchgelaufen ist!!!

			$this->setState('start', $app->getUserState($this->option . '.start', $this->getState('start', 0)));
			$this->setState('limit', $app->getUserState($this->option . '.limit', $this->getState('limit', 0)));
			$this->setState('catid', $app->getUserState($this->option . '.catid', $this->params->get('catid', array()))); // Wir holen den Standardwert aus den Parametern, weil wir ab hier nicht mehr die im Filter evtl. Voreingestellte Kategorie ausgeben wollen.
			$this->setState('tag', $app->getUserState($this->option . '.tag', $this->params->get('tag', array())));
			$this->setState('custom', $app->getUserState($this->option . '.custom', $this->getState('custom',array())));
			$this->setState('active_filters', $app->getUserState($this->option . '.active_filters', $this->getState('active_filters',array())));
			*/
			$this->setState('start', $app->getUserState($this->option . '.start', $this->params->get('start', 0)) );
			$this->setState('limit', $app->getUserState($this->option . '.limit', $this->params->get('limit', 0)) );
			$this->setState('catid', $app->getUserState($this->option . '.catid', $this->params->get('catid', array()))); // Wir holen den Standardwert aus den Parametern, weil wir ab hier nicht mehr die im Filter evtl. Voreingestellte Kategorie ausgeben wollen.
			$this->setState('tag', $app->getUserState($this->option . '.tag', $this->params->get('tag', array())));
			$this->setState('custom', $app->getUserState($this->option . '.custom', array() ));
			$this->setState('active_filters', $app->getUserState($this->option . '.active_filters', $active_filters ));

			// XHR zurücksetzen!
			$app->setUserState($this->option . '.xhr', 0);
		}

		$this->__state_set = true;
	}

	/**
	 * Method to get model state variables
	 *
	 * @param   string  $property  Optional parameter name
	 * @param   mixed   $default   Optional default value
	 *
	 * @return  mixed  The property where specified, the state object where omitted
	 *
	 * @since   2.0
	 */
	public function getState($property = null, $default = null)
	{
		if (!$this->__state_set)
		{
			// Protected method to auto-populate the model state.
			$this->populateState();

			// Set the model state set flag to true.
			$this->__state_set = true;
		}
		
		return $property === null ? $this->state : $this->state->get($property, $default);
	}

	/**
	 * Method to set model state variables
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $value     The value of the property to set or null.
	 *
	 * @return  mixed  The previous value of the property or null if not set.
	 *
	 * @since   2.0
	 */
	public function setState($property, $value = null)
	{
		return $this->state->set($property, $value);
	}


	/**
	 * Methode um die Modul-Stylesheets einzubinden. Entweder ein Override aus dem aktuellen Template-Verzeichnis, oder die Vorgabe CSS Datei aus /media.
	 *
	 * @param   string  $filename  Der Dateiname des Stylesheets
	 *
	 * @return  void
	 *
	 * @since   2.0
	 */
	public static function addStylesheet($filename)
	{
		$app = Factory::getApplication();
		$doc = Factory::getDocument();

		// Pfade zum suchen von CSS Dateien – werden auch für den URL benutzt!
		$paths = array(
					'/templates/' . $app->getTemplate() . '/html/mod_articles_head/css/', // 1. User Override
					'/media/mod_articles_head/css/' // 2. Basis
					);
		
		// Priorität der Dateierweiterung. Minified-Stylesheets haben die höhere Priorität.
		$extensions = array(
					'.min.css',
					'.css'
					);

		$parts = explode('.', $filename);

		foreach ($paths as $path)
		{
			foreach ($extensions as $ext)
			{
				if (File::exists(JPATH_ROOT . $path . $parts[0] . $ext))
				{
					$doc->addStylesheet(Uri::root(true) . $path . $parts[0] . $ext);
					break 2;
				}
			}
		}
	}


	/** 
	 * Ermittle die Anzahl der anzuzeigenden Beiträge. Je nach Moduleintellungen.
	 * 
	 * @param   \Joomla\Registry\Registry  &$params   Ein Objekt, dass die Model-Parameter enthält
	 * @param   Boolean  $all   Hole die Anzahl aller Beiträge, die mit den aktuellen Filtereinstellungen angezeigt werden.
	 * 
	 * @return   int  Anzahl der Beiträge
	 * 
	 * @since   1.5
	 */
	public function getItemsCount($all = false)
	{
		if($all) 
		{
			$count = $this->getList('all');
		}
		else 
		{
			$count = $this->getList(true);
		}


		if( $count === false )
		{
			return 0;
		}
		else 
		{
			return count($count);
		}
	}

	/**
	 * AJAX Controller – Wird von com_ajax aufgerufen und rendert die Ausgabe partiel.
	 * 
	 * @return   mixed  Das partiell gerenderte Modul (String) oder Boolesch false, wenn es nicht geklappt hat.
	 * 
	 * @since   1.5.0
	 */
	public static function renderAjax()
	{
		$app 	= Factory::getApplication();
		$input  = $app->input;
		$id 	= $input->get('modid', false, 'INT'); // Der Helper weiß sonst nicht, welches Modul gemeint ist – sprich welche Beiträge geladen werden sollen.
		
		if (!$id) return false;

		// ModuleHelper::getModuleById funktioniert hier nicht, weil ModuleHelper in diesem Context nur die Administrator-Module abholt (siehe: https://api.joomla.org/cms-3/classes/Joomla.CMS.Application.CMSApplication.html#method_getClientId)
		// $module = ModuleHelper::getModuleById((int)$id);

		$dbo = Factory::getDbo();
		$q	 = $dbo->getQuery(true);

		// Zugriffsrechte respektieren
		$levels = Factory::getUser()->getAuthorisedViewLevels(); // Ids der Joomla Zugriffsebenen, denen der aktuelle User angehört.
		asort($levels);

		// Veröffentlichungszeitraum respektieren
		$nowDate 	= Factory::getDate()->toSql(); // Aktuelles Datum als SQL DateTime String
		$nullDate   = $dbo->getNullDate(); // Null-Datum als SQL DateTime String

		$cond = array(
			$dbo->quoteName('id') . ' = ' . $id,
			$dbo->quoteName('published') . ' >= 1',
			$dbo->quoteName('access') . ' IN (' . implode(',', $levels) .')',
			'(publish_up = ' . $dbo->quote($nullDate) . ' OR publish_up <= ' . $dbo->quote($nowDate) . ')',
			'(publish_down = ' . $dbo->quote($nullDate) . ' OR publish_down >= ' . $dbo->quote($nowDate) . ')'
		);

		$q->select('*')
			->from($dbo->quoteName('#__modules'))
			->where($cond, 'AND')
			->limit(1);

		$dbo->setQuery($q);

		if ($module = $dbo->loadObject())
		{
			$option = 'modintro' . $id;

			$stateVars = array (
				'xhr' 		=> 1,
				'start' 	=> $input->get('start', 0, 'INT'),
				'catid' 	=> null,
				'tag'		=> null,
				'custom' 	=> null
			);

			$activeFilters = array();

			// Kategorie-Filter
			if($cat_filter = $input->get('catid', FALSE, 'INT')) 
			{	
				$stateVars['catid'] = $cat_filter;
				$activeFilters[] = 'catid';
			}

			// Schlagworte-Filter
			if($tag_filter = $input->get('tag', FALSE, 'INT')) 
			{
				$stateVars['tag'] = $tag_filter;
				$activeFilters[] = 'tag';
			}

			// Custom Fields
			if($custom_filter = $input->post->get('custom', '', 'raw')) 
			{
				// Unplausible (leere) Werte entfernen.
				foreach($custom_filter as $fieldid => $values)
				{
					// Unplausible (leere) Werte entfernen.
					if (!count(array_filter($values)))
					{
						unset($custom_filter[$fieldid]);
					}

					/* Dynamische Filteroptionen; Evtl. wurde eine neue Kategorie gewählt. 
						Es kann vorkommen, dass ein Filterwert gewählt wurde, der in den Beiträgen dieser Kategorie nicht existiert. 
						Da der Filterwert im Frontend auch nicht mehr angezeigt wird, kann das eine leere Ausgabe des Moduls erzeugen (keine Beiträge werden angezeigt, weil Filter aktiv sind, die es in der Kategorie nicht gibt).
						Hier wird geprüft, ob der aktive Wert des Eigenen-Felds in der Kategorie existiert, wenn nciht wird der Filter zurückgesetzt.
					*/
					if($cat_filter !== false)
					{
						$helper = new self($module);
						$helper->getState('catid', null); // populateState muss einmal laufen...
						$helper->setState('catid', $cat_filter);

						$options 		= $helper->getFilterOptions($fieldid);
						$filterParams 	= $helper->getFilterParams($fieldid);

						// IF spart hier ein bisschen Rechenzeit, wenn es ein range ohne auf-/abrunden ist. (Siehe switch unten, da wird bei range immer sortiert)
						if($helper->getCustomFilterType($fieldid) === 'range' && (bool)$filterParams->range_round_numbers)
						{
							$options = $helper->sortFilterOptions($options, $helper->getFilterParams($fieldid));

							if(!$values[0] < $options[0] || !end($values) > end($options))
							{
								unset($custom_filter[$fieldid]);
							}
						}
						else 
						{
							if(count(array_intersect($values, $options)) < 1)
							{
								unset($custom_filter[$fieldid]);
							}
						}

						/*
						switch($helper->getCustomFilterType($fieldid))
						{
							case 'range' :
								$options = $helper->sortFilterOptions($options, $helper->getFilterParams($fieldid));

								if($values[0] < $options[0] || end($values) > end($options))
								{
									unset($custom_filter[$fieldid]);
								}
							break;

							default :
								if(count(array_intersect($values, $options)) < 1)
								{
									unset($custom_filter[$fieldid]);
								}
						}
						*/
					}
				}


				if(count($custom_filter))
				{
					$stateVars['custom'] = $custom_filter;
					$activeFilters[] = 'custom';
				}				
			}

			foreach ($stateVars as $key => $val) 
			{
				$app->setUserState($option . '.' . $key, $val);
			}
			
			$app->setUserState($option . '.active_filters', $activeFilters);

			// Temporäre Modulparameter.
			// Diese existieren nur solange bis das Modul gerendert wurde.
			$params = new Registry($module->params);
			
			$params->set('style', 'none'); 	// ModuleChrome überschreiben; Das als Konfig. an ModuleHelper::renderModule zu übergeben funktioniert nicht. Habe noch nicht geschaut warum.
			$params->set('xhr', 1);
			
			$layout = explode(':',$params->get('layout', '_:default'));
			$prefix = $layout[1] !== 'default' ? $layout[1] : '';

			if($input->get('type','') === 'filter')
			{
				$params->set('render_type', 'filter'); 	// Für default.php Template; Partielle Ausgabe
				$params->set('layout', $prefix . '_ajaxfilters');
			}
			else
			{
				$params->set('render_type', 'items'); 	// Für default.php Template; Partielle Ausgabe
				$params->set('layout', $prefix . '_itemlist');
			}

			$module->params = $params->toString();

			// Modul rendern.
			return ModuleHelper::renderModule($module);
		}

		return false;
	}

	/**
	 * AJAX - Konfiguration für den Hyperlink „Mehr-laden” und die Items in der Paginierung.
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
	public function getAjaxLinkConfig() {

		$config = (object) [
			'url' 		=> Uri::root() . 'index.php', 							// Basis-URL, das JavaScript fügt alles weitere ein.
			'id'		=> $this->module->id, 									// Die Id des Moduls, das diese Funktion aufruft.
			'start' 	=> $this->getState('start', 0) + $this->getState('limit', 6),	 	// Start...
            'target' 	=> '#mod-intro-items-list-' . $this->module->id, 		// Ziel zum einhängen des neuen Content
			'replace' 	=> true // replace: Beiträge im Modul ersetzen – Nur bei Typ „Ein Button”, bei Typ „Paginierung” passiert das immer.
		];
		
		if ((int)$this->params->get('ajax_loadmore_type', 1) === 1
			&& (int)$this->params->get('ajax_replace_content', 0) === 0)
		{
			$config->replace = false;
		}
		
		if ($this->getState('active_filters', null))
		{
			foreach($this->getState('active_filters') as $filter)
			{
				$config->{$filter} = $this->getState($filter, array());
			}
		}
		
		// -- Post-Animationen
		if($this->params->get('ajax_post_animations', 0)) 
		{
			$config->animate 	= true;
			$config->aniclass 	= $this->params->get('ajax_post_animation_class','');
			$config->aniname 	= $this->params->get('ajax_post_animation_name','');
		}

		return $config;
	}


	/**
	 * Hole die Ids der Beiträge die auf einen Wert in einem „Eigene-Felder”-Filter zutreffen.
	 * 
	 * @param   int  $fieldid   Die Id des „Eigenen Felds”, nach welchen gefiltert wird.
	 * @param   Array  $values  Die Werte nach denen gefiltert wird.  
	 * @param   String  $type  Die Art des Filters, entspricht dem Template-Namen der Filter. Unterschieden wird in der Verarbeitung nur zwischen „range”/”html5range” und allen Anderen.
	 * 
	 * @return  Array  Ein Array, welches die Beitrags-Ids enthält, oder leer ist.
	 * 
	 * @since   1.7.4
	 * 
	 */
	private function getCustomFilterArticles($fieldid, $values, $type = 'default')
	{
		$dbo = Factory::getDbo();
		$query = $dbo->getQuery(true);

		switch($type)
		{
			case 'range' :
			case 'html5range' :

				// Workaround für Range-Filter, wenn von getItemsCount die Anzahl an Items pro Filter-Option angefordert wird – Das klappt nicht wirklich und muss neu, wird aber gebraucht um Fehler 500 zu vermeiden.
				/*
				if(!isset($values[1]))
				{
					$values[1] = $values[0];
				}
				*/
				
				$query->select('DISTINCT '. $dbo->quoteName('item_id'))
					->from($dbo->quoteName('#__fields_values'))
					->where($dbo->quoteName('field_id') .'='. $fieldid)
					->andWhere($dbo->quoteName('value') . ' >= ' . $values[0])
					->andWhere($dbo->quoteName('value') . ' <= ' . $values[1]);
			break;

			default : 
				$query->select('DISTINCT '. $dbo->quoteName('item_id'))
					->from($dbo->quoteName('#__fields_values'))
					->where($dbo->quoteName('field_id') .'='. $fieldid)
					->andWhere($dbo->quoteName('value') . ' IN (\'' . implode('\',\'', $values) . '\')');
		}

		if($this->params->get('ajax_filter_showempty', 0))
		{
			$query->orWhere($dbo->quoteName('value') . '=\'\'' );
		}

		$dbo->setQuery($query);
		
		// Das Exception Handling kann wieder raus – War nur für getItemsCount, weil die Abfrage für Range-Filter ungültige SQL Queries erzeugt hat!
		try
		{
			$result = $dbo->loadColumn();
		}
		catch (Exception $e)
		{
			echo $e->getMessage() . "\n";
			$result = array();
		}
		
		return $result;
		//return $query->__toString();
	}


	/**
	 * Ermittle den Typ (=Template-Name) eines „Eigene-Felder”-Filter zur weiteren Übergabe an getCustomFilterArticles
	 * 
	 * @param  int  $id  Die Id des Eigenen-Feldes
	 * 
	 * @return  String  Der Filter-Typ (Template-Name)
	 * 
	 * @since 2.0
	 */
	private function getCustomFilterType($id)
	{
		$filters = $this->params->get('ajax_filter', array());
		foreach($filters as $filter)
		{
			if((int)$filter->filter_field === (int)$id)
			{
				return $filter->template;
			}
		}
		return 'default';
	}


	/**
	 * Hole die Filtereinstellungen aus den Modulparametern anhand der Feld-Id
	 * 
	 * @param  int  $id  Die Id des Eigenen-Feldes
	 * 
	 * @return  Object  Ein Objekt, das die Einstellungen eines Filters enthält
	 * 
	 * @since 2.0
	 */
	public function getFilterParams($id) 
	{
		$filters = $this->params->get('ajax_filter', array());
		foreach($filters as $filter)
		{
			if((int)$filter->filter_field === (int)$id)
			{
				return $filter;
			}
		}
		return null;
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
	public function getList($count = false)
	{
		$app = Factory::getApplication();

		// Get an instance of the generic articles model
		$model = BaseDatabaseModel::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$model->setState('params', $app->getParams());

		// Setze die Filter im Model
		if($count === 'all')
		{
			$model->setState('list.start', 0);
			$model->setState('list.limit', 0);
		}
		else
		{
			$model->setState('list.start', $this->getState('start', 0));
			$model->setState('list.limit', $this->getState('limit', 6));
		}

		$model->setState('filter.category_id', $this->getState('catid', array()));
		$model->setState('filter.tag', $this->getState('tag', array()));

		$model->setState('filter.published', 1);

		// Access filter
		$access	 	= !ComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
		$model->setState('filter.access', $access);

		// Filter by language
		$model->setState('filter.language', $app->getLanguageFilter());

        // Featured switch
		$model->setState('filter.featured', $this->params->get('show_featured', 'show'));

		/*
			Aktuellen Beitrag verbergen (aus Joomla 4 mod_articles_news Helper)
			Kollidiert mit Custom Fields Filter – siehe alte Iplementierung nach $model->getItems
			 
		if ($params->get('hidecurrentarticle', false)
			&& $app->input->get('option') === 'com_content'
			&& $app->input->get('view') === 'article')
		{
			$model->setState('filter.article_id', $app->input->get('id', 0, 'UINT'));
			$model->setState('filter.article_id.include', false);
		}
		*/

		// Set ordering
		$ordering = $this->params->get('ordering', 'a.publish_up');
		$model->setState('list.ordering', $ordering);

		if (trim($ordering) === 'rand()')
		{
			$model->setState('list.ordering', Factory::getDbo()->getQuery(true)->Rand());
		}
		else
		{
			$model->setState('list.direction', $this->params->get('direction', 'ASC'));
		}


		/*
			Custom Fields Filter
		*/

		if (count( $this->getState('custom', array()) ))
		{
			$c = 0;
			$filterArticles = array();
			$tmpArticles = array();

			//foreach ($params->get('filter_custom') as $id => $values)
			foreach ($this->getState('custom') as $id => $values)
			{
				if (count($values))
				{
					$tmpArticles = $this->getCustomFilterArticles($id, $values, $this->getCustomFilterType($id));
				}
			
				if($c == 0)
				{
					$filterArticles = $tmpArticles;
				}
				else
				{
					$filterArticles = array_intersect($filterArticles, $tmpArticles); // Nur Ids übernehmen, die in beiden Arrays vorkommen.
				}
				$c++;
				
			}
			
			
			if ($c > 0)
			{
				if (count($filterArticles))
				{
					$model->setState('filter.article_id', $filterArticles);
					$model->setState('filter.article_id.include', true);
				}
				else
				{
					$model->setState('filter.article_id', array());
					$model->setState('filter.article_id.include', true);
				}
			}
		}

		// Retrieve Content
		$items = $model->getItems();

		// Aktuellen Beitrag im Modul verbergen.
		if($this->params->get('hidecurrentarticle', 0)
			&& $app->input->get('option') === 'com_content'
			&& $app->input->get('view') === 'article')
		{
			$id = explode(":", $app->input->get('id', null, 'INT')); // „Artikel Slug” zu einem Array mit id und Titel

			foreach($items as $i => $item) 
			{
				if ($item->id == $id[0])
				{
					array_splice($items, $i, 1);
					break;
				}
			}
		}

		// Wir benötigen nur die Anzahl der Items und brechen an dieser Stelle ab.
		if ($count) 
		{
			return $items;
		}

		/**
			CRu./TSö.: 
			Check if we should trigger additional plugin events – aus mod_articles_news
		*/
		$triggerEvents = $this->params->get('triggerevents', 1);

		foreach ($items as &$item)
		{
			$this->setItemLink($item);
			
			//$item->introtext = HTMLHelper::_('content.prepare', $item->introtext, '', 'mod_articles_news.content');
			$item->introtext = HTMLHelper::_('content.prepare', $item->introtext, '', 'mod_articles_head.content');

			if (!$this->params->get('image'))
			{
				$item->introtext = preg_replace('/<img[^>]*>/', '', $item->introtext);
			}

			// Show the Intro/Full image field of the article
			if ($this->params->get('preview_image'))
			{
				$images = json_decode($item->images);
				$item->imageSrc = '';
				$item->imageAlt = '';
				$item->imageCaption = '';
				if ($this->params->get('img_intro_full') === 'intro' && !empty($images->image_intro))
				{
					$item->imageSrc = htmlspecialchars($images->image_intro, ENT_COMPAT, 'UTF-8');
					$item->imageAlt = htmlspecialchars($images->image_intro_alt, ENT_COMPAT, 'UTF-8');
					if ($images->image_intro_caption)
					{
						$item->imageCaption = htmlspecialchars($images->image_intro_caption, ENT_COMPAT, 'UTF-8');
					}
				}
				elseif ($this->params->get('img_intro_full') === 'full' && !empty($images->image_fulltext))
				{
					$item->imageSrc = htmlspecialchars($images->image_fulltext, ENT_COMPAT, 'UTF-8');
					$item->imageAlt = htmlspecialchars($images->image_fulltext_alt, ENT_COMPAT, 'UTF-8');
					if ($images->image_intro_caption)
					{
						$item->imageCaption = htmlspecialchars($images->image_fulltext_caption, ENT_COMPAT, 'UTF-8');
					}
				}
			}


			/**
				CRu./TSö.: 
				Check if we should trigger additional plugin events – aus mod_articles_news
			*/
			if ($triggerEvents)
			{
				$item->text = '';
				$app->triggerEvent('onContentPrepare', array ('com_content.article', &$item, &$this->params, 0));

				$results				 = $app->triggerEvent('onContentAfterTitle', array('com_content.article', &$item, &$this->params, 0));
				$item->afterDisplayTitle = trim(implode("\n", $results));

				$results					= $app->triggerEvent('onContentBeforeDisplay', array('com_content.article', &$item, &$this->params, 0));
				$item->beforeDisplayContent = trim(implode("\n", $results));

				$results				   = $app->triggerEvent('onContentAfterDisplay', array('com_content.article', &$item, &$this->params, 0));
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
	 * Generiert den Modul-Weiterlesen URL
	 * 
	 * @param   \Joomla\Registry\Registry  &$params   Ein Registry Objekt, das die Modulparameter enthält.
	 * 
	 * @return   string   Ein String, welcher den URL für Modul-Weiterlesen enthält oder ein leerer String, wenn kein URL generiert werden konnte.
	 * 
	 * @since 1.10.0
	*/
	public function getModuleReadmoreUrl()
	{
		$type 	= $this->params->get('module_readmore_type', 0);
		$app 	= Factory::getApplication();
		$url  	= '';

		switch($type) 
		{
			case 'url' :
				$url = $this->params->get('module_readmore_url', '');
			break;

			case 'category' :
				$catids = $this->params->get('catid', array());
				$url 	= Route::_( ContentHelperRoute::getCategoryRoute(reset($catids)) );
			break;

			case 'menuitem' :
				$item = $this->params->get('module_readmore_menuitem', '');

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
	 * Generiert den Weiterlesen URL eines Beitrags
	 * 
	 * @param   stdClass  &$item   Ein Objekt, welches ein Beitrags-Model repräsentiert.
	 * 
	 * @return   string   Ein String, welcher den Weiterlesen-URL repräsentiert oder ein leerer String, wenn gar kein URL ermittelt werden konnte.
	 * 
	 * @since   1.7.4
	 */
	public function setItemLink(&$item)
	{
		$attribs 		= new Registry($item->attribs);
		$item->readmore = 0; // Weiterlesen anzeigen - muss Integer sein!

		$item->link_blank = $attribs->get('xfields_readmore_blank', 0);

		switch($attribs->get('xfields_readmore_override', ''))
		{
			case 'url' :
				$item->readmore = 1;
				$item->link = $attribs->get('xfields_readmore_override_url', '');
			break;

			case 'menuitem' :
				$menu_item = Factory::getApplication()->getMenu()->getItem($attribs->get('xfields_readmore_override_menuitem', 0));

				if($menu_item)
				{
					$item->readmore = 1;

					if($menu_item->flink)
					{
						$item->link = $menu_item->flink;
					}
					else
					{
						$item->link = Route::_($menu_item->link . '&Itemid=' . $menu_item->id);
					}
				}
			break;

			case 'article' :
			default : 

				$override = $attribs->get('xfields_readmore_override_article', 0); // (int) 0 oder (int) Beitrags-Id

				if ($override !== 0)
				{
					$model = BaseDatabaseModel::getInstance('Article', 'ContentModel');
					$article = $model->getItem($override);
				}
				else 
				{
					$article =& $item;
				}

				if ((int)$article->state === 1)
				{
					$item->readmore 	= strlen(trim($article->fulltext));

					$access	 	= !ComponentHelper::getParams('com_content')->get('show_noauth');
					$authorised = Access::getAuthorisedViewLevels(Factory::getUser()->get('id'));
			
					$article->slug = $article->id . ':' . $article->alias;

					if ($access || in_array($article->access, $authorised))
					{
						// We know that user has the privilege to view the article
						$item->link	 = Route::_(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language));
						$item->linkText = Text::_('MOD_ARTICLES_NEWS_READMORE');
					}
					else
					{
						$item->link = new Uri(Route::_('index.php?option=com_users&view=login', false));
						$item->link->setVar('return', base64_encode(ContentHelperRoute::getArticleRoute($article->slug, $article->catid, $article->language)));
						$item->linkText = Text::_('MOD_ARTICLES_NEWS_READMORE_REGISTER');
					}
				}
		}
	}
	

	/** 
	 * Sortiert je nach Moduleinstellungen die Optionen, die von einem Filter anzeigt werden.
	 * 
	 * @param   object  &$options   Ein Array, das die Filteroptionen (Werte) enthält.
	 * @param   object  $filterParams   Ein Objekt, das die Filterkonfiguration aus den Moduleinstellungen enthält.
	 * 
	 * @return   array  Ein Array, welches die sortierten Filteroptionen enthält
	 * 
	 * @since   1.11.0
	 */

	public static function sortFilterOptions(&$options, &$filterParams) 
	{
		$sorted = array();

		switch ($filterParams->filter_type)
		{
			case 'category' :
			case 'tag' :
				foreach($options as $opt) 
				{
					switch($filterParams->filter_type)
					{
						case 'category' :
							$model = Categories::getInstance('content');
							$sorted[$opt] = $model->get($opt)->title;
						break;

						case 'tag' :
							$model = new TagsHelper();
							$sorted[$opt] = $model->getTagNames(array($opt))[0];
						break;
					}
					if ($filterParams->filter_sorting == 'asc'
						|| $filterParams->filter_sorting == 'desc')
					{
						setLocale(LC_COLLATE, Factory::getLanguage()->getLocale()[0]);
						uasort($sorted, 'strcoll');
			
						if ($filterParams->filter_sorting == 'desc')
						{
							$sorted = array_reverse($sorted);
						}
					}
				}
			break;

			case 'custom' : 
				$dbo = Factory::getDbo();
				$query = $dbo->getQuery(true);

				$query->select($dbo->quoteName('fieldparams'))
					->from('#__fields')
					->where($dbo->quoteName('id') . '=' . $filterParams->filter_field);

				if ($params = $dbo->setQuery($query)->loadResult())
				{
					//die(var_dump($params));
					$params = new Registry($params);

					switch($params->get('filter', ''))
					{
						case 'integer' :
						case 'float' :	

							if ($filterParams->filter_sorting === 'asc'
								|| $filterParams->filter_sorting === 'desc')
							{
								sort($options, SORT_NUMERIC);

								if ($filterParams->filter_sorting === 'desc')
								{
									$options = array_reverse($options);
								}
							}
						break;
						
						default : // Werte werden als String behandelt
							if ($filterParams->filter_sorting === 'asc'
								|| $filterParams->filter_sorting === 'desc')
							{
								setLocale(LC_COLLATE, Factory::getLanguage()->getLocale()[0]);
								uasort($options, 'strcoll');
					
								if ($filterParams->filter_sorting == 'desc')
								{
									$options = array_reverse($options);
								}
							}
					}

					foreach($options as $opt)
					{
						$sorted[(string)$opt] = $opt;
					}
				}

			break;
		} 

		return $sorted;
	}


	public function getFilterDefaultValue ($type) 
	{
		$filterConfig = $this->params->get('ajax_filter', array());

		foreach ($filterConfig as $filter)
		{
			if ($filter->filter_type === $type) 
			{
				$prop = 'filter_' . $type . '_default';
				return $filter->{$prop};
			}
		}

		return '';
	}


	/** 
	 * Stellt die Konfiguration für einen Eigenes-Feld-Filter zusammen, und gibt diese als Array zurück.
	 * 
	 * @param   int  fieldid   Ein Integer-Wert, der die Id des Eigenen-Felds repräsentiert
	 * 
	 * @return   Array  Ein Array, welches die Filteroptionen enthält, oder leer ist.
	 * 
	 * @since   2.0
	 */
	public function getFilterOptions ($fieldid)
	{
		// Hole die Optionen
		$options = array();

		// Ist dieses Feld in den im Modul gewählten Kategorien vorhanden?
		$dbo 	= Factory::getDbo();
		$query 	= $dbo->getQuery(true);

		$query->select($dbo->quoteName('category_id'))
			->from($dbo->quoteName('#__fields_categories'))
			->where($dbo->quoteName('field_id') . '=' . $fieldid . ' OR ' . $dbo->quoteName('category_id') . "=''");

		$dbo->setQuery($query);

		$intersection = array_intersect($this->params->get('catid', array()), $dbo->loadColumn());

		if(count($intersection) > 0) // Das Feld ist vorhanden; Werte holen.
		{
			$query->clear();

			if( !$this->params->get('ajax_dynamic_filteroptions', 0)) 
			{
				$catids = $this->params->get('catid', array(0));
			}
			else 
			{
				$catids = $this->getState('catid', array(0));
			}
			
			if(count($catids) > 1)
			{
				$catWhere = ' IN (' . implode(',', $catids) . ')'; 
			}
			else 
			{
				$catWhere = ' = ' . $catids[0];
			}
			
			$query->select('DISTINCT ' . $dbo->quoteName('value'))
				->from('#__fields_values v')
				->innerJoin('#__content c ON ' . $dbo->quoteName('v.item_id') . ' = ' . $dbo->quoteName('c.id'))
				->where($dbo->quoteName('v.field_id') . ' = ' . $fieldid)
				->andWhere($dbo->quoteName('c.catid') . $catWhere);
			
			$options = $dbo->setQuery($query)->loadColumn();
		}

		return $options;
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
	public function getFilterList () 
	{
		$filterConfig 	= $this->params->get('ajax_filter', array());
		$filterList 	= array();

		foreach($filterConfig as $filterParams)
		{
			$filter 					= new \stdClass();
			$filter->type       		= $filterParams->filter_type;
			$filter->label      		= $filterParams->filter_label;
			$filter->multiple 			= $filterParams->multiple;
			$filter->hide_option_all 	= $filterParams->hide_option_all;
			$filter->option_all_label 	= $filterParams->option_all_label != '' ? $filterParams->option_all_label : Text::_('JALL');
			$filter->template 			= $filterParams->template;
			$filter->show_items 		= $filterParams->show_items_count;
			$filter->options    		= array();	// Frontend Auswahl
			$filter->param_name 		= null; // Wenn sich der Filter auf einen Modul-Parameter bezieht, wird der hier gespeichert.
			$filter->default_value 		= null;
			$filter->current_value 		= null;

			switch($filter->type)
			{
				case "category" :
					$filter->param_name = 'catid';

					// Im Filter voreingestellte Kategorie
					if ($filterParams->filter_category_default !== '')
					{
						$filter->default_value 	= $filterParams->filter_category_default;
						// Setzte Zustand
						//$this->setState($filter->param_name, array($filter->default_value));
					}

					$options = $this->params->get($filter->param_name, array());

					// Aktuelle Werte
					$filter->current_value = $this->getState($filter->param_name, null);
				break;

				case "tag" :
					$filter->param_name = 'tag';
					
					if($filterParams->filter_tag_default != '')
					{
						$filter->default_value 	= $filterParams->filter_tag_default;
						// Setzte Zustand
						//$this->setState($filter->param_name, array($filter->default_value));
					}

					$options = $this->params->get($filter->param_name, array());

					// Aktuelle Werte
					$filter->current_value = $this->getState($filter->param_name, null);
				break;

				case "custom" :
					$filter->param_name 	= 'custom';
					$filter->filter_field 	= $filterParams->filter_field;

					// Aktuelle Werte
					$filter->current_value = null;
					if($values = $this->getState($filter->param_name, null))
					{
						foreach($values as $id => $value)
						{
							if((int)$id === (int)$filter->filter_field)
							{
								$filter->current_value = $value;
							}
						}
					}

					$options = $this->getFilterOptions($filter->filter_field);
				break;
			}
			// Filter sortieren
			$options = self::sortFilterOptions($options, $filterParams); // returns array("value" => "title", ... )

			// Ist dieser Filter aktiv?
			$filter->is_active = in_array($filter->param_name, $this->getState('active_filters', array()));

			// Setzte den Wert für das HTML Attribut "name"...
			$filter->field_name = $filter->param_name;
			if($filter->type === 'custom')
			{
				$filter->field_name .= '-' . $filter->filter_field;
			}

			// Feld-Id 
			$filter->field_id = $filter->field_name . '-' . $this->module->id;

			// ... und bei „Mehrfachauswahl” mache aus dem Feld ein Array.
			if($filter->multiple 
				|| $filter->template === 'range'
				|| $filter->template === 'html5range') 
			{
				$filter->field_name .= '[]';

				// ... und bei range, speichere die Informationen zur Formatierung von Zahlen
				if($filter->template === 'range')
				{
					// Anzahl Griffe
					$filter->range_handles = $filterParams->range_handles;

					// Werte auf-/abrunden?
					$filter->range_round_numbers = $filterParams->range_round_numbers;

					// Zahlenformatierung
					$format = array(
						'decimals' => $filterParams->range_decimals,
						'mark' => $filterParams->range_mark,
						'thousand' => $filterParams->range_thousand,
						'prefix' => $filterParams->range_prefix,
						'suffix' => $filterParams->range_suffix
						);

					$filter->range_format = json_encode($format, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
				}

			}
			
			// Daten für die Filtergruppe zusammenstellen – Ein Filter ist in einer Gruppe.
			$filter->group_data = array(
				"param" => $filter->param_name, 
				"type" 	=> $filter->template,
				"field" => $filter->field_name,
				"id" 	=> !empty($filter->filter_field) ? $filter->filter_field : ''
			);
			$filter->group_data = "data-filtergroup='" . json_encode((object) $filter->group_data) . "'";


			$ajaxConfig 			= $this->getAjaxLinkConfig();
			$ajaxConfig->filter 	= true;	// Dem AJAX-Script mitteilen, dass ein Filter angeklicht wurde (sonst wird die Filter-Schaltfläche beim Anklicken aus dem DOM entfernt)
			$ajaxConfig->s 			= 0;	// Start (ab dem 1. Beitrag)
			$ajaxConfig->replace 	= true; // Inhalt im Modul ersetzen
			
			$filter->reset_option = new \stdClass();
			$filter->reset_option->ajax_config = $ajaxConfig;
			if($filter->type === 'custom')
			{
				$filter->reset_option->ajax_config->{$filter->param_name} = array($filter->filter_field => array());
			}
			else
			{
				$filter->reset_option->ajax_config->{$filter->param_name} = array();
			}
			$filter->reset_option->ajax_json = json_encode($filter->reset_option->ajax_config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

			$o = 0;
			foreach($options as $value => $title) 
			{
				$filterOption = new \stdClass();
				$filterOption->raw_value 	= $value;
				$filterOption->ajax_config 	= $ajaxConfig;
				$filterOption->title 		= $title;
				$filterOption->id			= $filter->field_name . '-' . $o . '-' . $this->module->id; 	

				switch($filter->type)
				{
					case 'category' :
						$filterOption->ajax_config->{$filter->param_name} = array($value);

						$filterOption->is_default = false;
						if($filter->default_value && $filter->default_value == $value)
						{
							$filterOption->is_default = true;
						}
					break;

					case 'tag' :
						$filterOption->ajax_config->{$filter->param_name} = array($value);
					break;

					case 'custom' :
						$filterOption->ajax_config->{$filter->param_name}[$filter->filter_field] = array($value);
					break;
				}

				$filterOption->ajax_json = json_encode($filterOption->ajax_config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);

				
				if($filterParams->show_items_count
					&& $filter->template !== 'range') 
				{
					$helper = new self($this->module);
					$helper->populateState();

					if($filter->param_name === 'custom')
					{
						$temp = $helper->getState($filter->param_name);

						if(is_array($temp))
						{
							$temp[(string)$filter->filter_field] = array($value);
						}
						else 
						{
							$temp = array($filter->filter_field => array($value));
						}
						$helper->setState($filter->param_name, $temp);

						
						//$helper->setState($filter->param_name, array($filter->filter_field => array($value)) );	
					}
					else
					{
						$helper->setState($filter->param_name, array($value));
					}

					$filterOption->items_count = $helper->getItemsCount('all');
				}

				$filter->options[] = $filterOption;
				$o++;
			} // End foreach



			$filterList[$filter->field_name] = $filter;
		} // End foreach
	
		return $filterList;
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
	public function getPaginationList()
	{
		$params = $this->params;

		$pages = new \stdClass();
		$pages->items 	= (int) $this->getItemsCount('all'); 			// Anzahl Beiträge
		$pages->limit 	= $this->getState('limit'); // $app->getUserState($this->option .'.limit', null) ? (int) $app->getUserState($this->option .'.limit', 0) : (int) $params->get('count', 0);				// Anzahl Beiträge auf einer Seite
		$pages->total 	= (int) ceil($pages->items / $pages->limit);	// Gesamtseitenzahl
		$pages->current = (int) ceil(($this->getState('start') - $pages->limit + 1) / $pages->limit + 1); // Aktuelle Seite

		$list = array(
			"start",
			"previous",
			"next",
			"end"
		);

		// Pagination-Layout, wie in Moduloptionen angegeben.
		$layout = $params->get('ajax_pagination_layout', array());
	
		foreach($list as $name) 
		{
			$config = $this->getAjaxLinkConfig();
			$config->replace = true;
				
			switch($name) 
			{
				case "start" :
					if($config->start - $pages->limit == 0) 
					{
						$config->start = null;
					}
					else {
						$config->start = 0;
					}
				break;
	
				case "previous" :
					if($config->start - ($pages->limit * 2) < 0) 
					{
						$config->start = null;
					}
					else 
					{
						$config->start = $config->start - ($pages->limit * 2);
						$config->start = $config->start <= 0 ? 0 : $config->start;
					}
				break;
	
				case "next" :
					if($config->start >= $pages->items) 
					{
						$config->start = null; 
					}
				break;
	
				case "end" :
					if($pages->total === $pages->current || $pages->total === 0)
					{
						$config->start = null;
					}
					else 
					{
						$config->start = $pages->limit * ($pages->total - 1);
					}
				break;
			}

			$list[$name] = new \stdClass();
			$list[$name]->show      = in_array($name, $layout);
			$list[$name]->config 	= $config->start === null ? "" : "data-modintroajax='" . json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK) . "'";
			$list[$name]->disabled 	= $config->start === null ? true : false;
			$list[$name]->text 		= Text::_("MODARH_PAGINATION_" . strtoupper($name) . "_LABEL");
		}
	
		$list["pages"] 			= new \stdClass();
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
				$list["pages"]->options[$i] = new \stdClass();

				if($i + 1 !== $pages->current)
				{
					$config->start = $pages->limit * $i;
					$list["pages"]->options[$i]->current = false;
				}
				else 
				{
					$config->start = null;
					$list["pages"]->options[$i]->current = true;
				}

				$list["pages"]->options[$i]->config     = $config->start === null ? "" : "data-modintroajax='" . json_encode($config, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK) . "'";
				$list["pages"]->options[$i]->disabled 	= $config->start === null ? true : false;
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
