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

// Include the news functions only once
JLoader::register('ModArticlesHeadHelper', __DIR__ . '/helper.php');


// -- Gesamtanzahl Items abfragen, dazu parameter überschreiben:
$tmpParams = clone $params;
$tmpParams->set('start', 0);
$tmpParams->set('count', 0);
$fullItemsCount = ModArticlesHeadHelper::getItemsCount($tmpParams);
unset($tmpParams);


// -- Für AJAX; Start und Anzahl von Items:
$begin		= $params->get('start',0);
$limit 		= $params->get('count',0);


// -- Liste der Beiträge
$list		= ModArticlesHeadHelper::getList($params);


// -- Modulklassen-Suffix
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');


// -- Lade das Beispiel-Stylesheet
if($params->get('load_module_css', 0)) {
	JFactory::getApplication()->getDocument()->addStylesheet(JUri::root() . 'media/mod_articles_head/css/mod-intro.css');
}

// -- Lade das AJAX Controller-Script
if($params->get('ajax_loadmore', 0)) {
	JFactory::getApplication()->getDocument()->addScript(JUri::root() . 'media/mod_articles_head/js/mod_intro_ajax.min.js');

	if($params->get('ajax_post_animations', 0) && $params->get('ajax_post_animations_load_animatedcss', 0)) {
		JFactory::getApplication()->getDocument()->addStylesheet(JUri::root() . 'media/mod_articles_head/css/animate.css');
	}

	$ajaxInitScript = <<<SCRIPT
(function($) {
	$(function() {
	   $('#mod-intro-$module->id').modintroajax(); 
	});
})(jQuery);
SCRIPT;

	JFactory::getApplication()->getDocument()->addScriptDeclaration($ajaxInitScript);
}

// -- Lade die Vorschauvideo-Scripts etc.
if($params->get('introvideos', 0)) {
	JFactory::getApplication()->getDocument()->addScript(JUri::root() . 'media/mod_articles_head/js/mod_intro_video.min.js');

	$videoInitScript = <<<SCRIPT
(function($) {
	$(function() {
		$('#mod-intro-$module->id').modintrovideo();
	});
})(jQuery);
SCRIPT;

	JFactory::getApplication()->getDocument()->addScriptDeclaration($videoInitScript);

	// -- Lade Featherlight.js
	if($params->get('featherlightbox', 0)) {
		JFactory::getApplication()->getDocument()->addStylesheet(JUri::root() . 'media/mod_articles_head/css/featherlight.min.css');
		JFactory::getApplication()->getDocument()->addScript(JUri::root() . 'media/mod_articles_head/js/featherlight.min.js');
	}
}

// -- Lade das Layout
$layout = $params->get('layout','default');
require JModuleHelper::getLayoutPath('mod_articles_head', $layout);
