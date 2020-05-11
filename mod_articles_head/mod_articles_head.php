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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

// Include the news functions only once
JLoader::register('ModArticlesHeadHelper', __DIR__ . '/helper.php');

$app = Factory::getApplication();
$doc = $app->getDocument();

// Voreingestellter Kategorie-Filter
if($params->get('ajax', 0) != 1) {
	$filterSettings = $params->get('ajax_filter', array());
	foreach($filterSettings as $filter)
	{
		if ($filter->filter_type === 'category' && $filter->filter_category_default != '')
		{
			$params->set('catid', $filter->filter_category_default); // Das wird in die Modulparameter geschrieben, damit das Modul nur das anzeigt, was es soll.
			$params->set('filter_catid', $filter->filter_category_default); // Das wird für den Button Loadmore benötigt damit sich dessen Konfiguration entsprechend anpasst, wenn gefiltert wird.
		}
	}
}

// -- Liste der Beiträge
$list = ModArticlesHeadHelper::getList($params);

// -- Modulklassen-Suffix
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

// -- Lade das Beispiel-Stylesheet
if ($params->get('load_module_css', 0)) 
{
	$doc->addStylesheet(Uri::root() . 'media/mod_articles_head/css/mod-intro.css');
}

// -- Lade das AJAX Controller-Script
if($params->get('ajax_enable', 0)) 
{
	// Die Sprachdatei von com_content wird benötigt, wenn der Info-Block angezeigt wird, und Beiträge per AJAX nachgeladen werden.
    Factory::getLanguage()->load('com_content'); 

    HTMLHelper::_('jquery.framework', true, true); // -- Sicherstellen, dass jQuery vorher geladen wird.
	$doc->addScript(Uri::root() . 'media/mod_articles_head/js/mod_intro_ajax.min.js');

	if ($params->get('ajax_post_animations', 0) 
			&& $params->get('ajax_post_animations_load_animatedcss', 0)) 
	{
		$doc->addStylesheet(Uri::root() . 'media/mod_articles_head/css/animate.css');
	}

    $ajaxRequestConfig = json_encode(ModArticlesHeadHelper::getAjaxLinkConfig($module), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
	$ajaxScrollToItems = $params->get('ajax_scroll', 0);
	
	$ajaxScrollOffsets = (object) array(
		"query" 	=> htmlspecialchars($params->get('ajax_scroll_offset_query', '')),
		"manual" 	=> $params->get('ajax_scroll_offset_manual', 0, 'INT')
	);

	$ajaxInitScript = <<<SCRIPT
;(function($) {
	$(function() {
	   $('#mod-intro-$module->id').modintroajax({ajaxConfig : JSON.parse('$ajaxRequestConfig'), scroll : {enabled : $ajaxScrollToItems, offsetQuery : '$ajaxScrollOffsets->query', offsetManual : $ajaxScrollOffsets->manual}});
	});
})(jQuery);
SCRIPT;

	$doc->addScriptDeclaration($ajaxInitScript);
}


// -- Lade die Vorschauvideo-Scripts etc.
if($params->get('introvideos', 0)) {
    
    HTMLHelper::_('jquery.framework', true, true); // Sicherstellen, dass jQuery vorher geladen wird.
	$doc->addScript(Uri::root() . 'media/mod_articles_head/js/mod_intro_video.min.js');

	$videoInitScript = <<<SCRIPT
;(function($) {
	$(function() {
		$('#mod-intro-$module->id').modintrovideo();
	});
})(jQuery);
SCRIPT;

	$doc->addScriptDeclaration($videoInitScript);

	// -- Lade Featherlight.js
	if($params->get('featherlightbox', 0)) 
	{
		$doc->addStylesheet(Uri::root() . 'media/mod_articles_head/css/featherlight.min.css');
		$doc->addScript(Uri::root() . 'media/mod_articles_head/js/featherlight.min.js');
	}
}

// -- Lade das Layout
$layout = $params->get('layout','default');
require ModuleHelper::getLayoutPath('mod_articles_head', $layout);
