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

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

// Helper hinzufügen
JLoader::register('ModArticlesHeadHelper', __DIR__ . '/helper.php');

$Helper = new ModArticlesHeadHelper($module);
$app = Factory::getApplication();
$doc = $app->getDocument();


// Modulklassen-Suffix
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8'); 

// Die Sprachdatei von com_content wird geladen und benötigt für: den Infoblock, „Weiterlesen”.
Factory::getLanguage()->load('com_content'); 

// Lade das Beispiel-Stylesheet
if ($params->get('load_module_css', 0)) 
{	
	$Helper->addStylesheet('mod-intro.min.css');
}

// -- Lade das AJAX Controller-Script
if($params->get('ajax_enable', 0)
	&& !$params->get('render_partial', 0)) 
{
	HTMLHelper::_('jquery.framework', true, true); // Sicherstellen, dass jQuery vorher geladen wird.
	
	$Helper->addStylesheet('loading-spinner.min.css');
	$doc->addScript(Uri::root() . 'media/mod_articles_head/js/mod_intro_ajax.min.js'); // AJAX Controller-Script

	// Animate.css laden
	if ($params->get('ajax_post_animations', 0) 
			&& $params->get('ajax_post_animations_load_animatedcss', 0)) 
	{
		$Helper->addStylesheet('animate.min.css');
	}

	// Konfiguration für XHR
    $ajaxRequestConfig = json_encode($Helper->getAjaxLinkConfig(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_NUMERIC_CHECK);
	$ajaxScrollToItems = $params->get('ajax_scroll', 0);
	
	// Offset-Elemente für automatisches scrollen
	$ajaxScrollOffsets = (object) array(
		"query" 	=> htmlspecialchars($params->get('ajax_scroll_offset_query', '')),
		"manual" 	=> $params->get('ajax_scroll_offset_manual', 0, 'INT')
	);

	// AJAX-Controller init-Script
	$ajaxInitScript = <<<SCRIPT
(function($) {
	$(function() {
	   $('#mod-intro-$module->id').modintroajax({ajaxConfig : $ajaxRequestConfig, scroll : {enabled : $ajaxScrollToItems, offsetQuery : '$ajaxScrollOffsets->query', offsetManual : $ajaxScrollOffsets->manual}});
	});
})(jQuery);
SCRIPT;

	$doc->addScriptDeclaration($ajaxInitScript);
}


// Lade die Vorschauvideo-Scripts etc.
if($params->get('introvideos', 0)
	&& !$params->get('render_partial', 0)) 
{
    HTMLHelper::_('jquery.framework', true, true); // Sicherstellen, dass jQuery vorher geladen wird.
	
	// Vorschau Videos Script laden
	$Helper->addStylesheet('introvideos.min.css');
	$doc->addScript(Uri::root() . 'media/mod_articles_head/js/mod_intro_video.min.js');

	// Vorschau Videos init-Script
	$videoInitScript = <<<SCRIPT
(function($) {
	$(function() {
		$('#mod-intro-$module->id').modintrovideo();
	});
})(jQuery);
SCRIPT;

	$doc->addScriptDeclaration($videoInitScript);

	// Featherlight.js laden
	if($params->get('featherlightbox', 0)) 
	{
		$Helper->addStylesheet('featherlight.min.css');
		$doc->addScript(Uri::root() . 'media/mod_articles_head/js/featherlight.min.js');
	}
}


if($params->get('rendertype','items') === 'items')
{
	// Liste der Beiträge abholen
	$list = $Helper->getList();
}

// Lade das Layout
require ModuleHelper::getLayoutPath('mod_articles_head', $params->get('layout', 'default'));