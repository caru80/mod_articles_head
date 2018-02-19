<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_news
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the news functions only once
JLoader::register('ModArticlesHeadHelper', __DIR__ . '/helper.php');



$begin		= $params->get('start',0);
$limit 		= $params->get('count',0);

$list		= ModArticlesHeadHelper::getList($params);


// Gesamtanzahl Items abfragen, dazu parameter überschreiben:
$params->set('start', 0);
$params->set('count', 0);

$fullItemsCount = ModArticlesHeadHelper::getItemsCount($params);

// Parameter wieder zurücksetzen:
$params->set('start', $begin);
$params->set('count', $limit);




$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

$layout = $params->get('layout','default');

require JModuleHelper::getLayoutPath('mod_articles_head', $layout);
