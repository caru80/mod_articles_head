<?php
/**
 * @package        HEAD. Article Module
 * @version        1.8.5
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


// -- Videos
$videos = (object)array("intro" => $attribs->get('xfields_teaser_video'), "full" => $attribs->get('xfields_full_video'));

/**
	Achtung: Joomla Dateiauswahlfelder haben einen Standardwert vom Typ string mit dem Wert -1, wenn nichts ausgewählt wurde. Und den Typ null, wenn noch nie etwas ausgewählt wurde.
*/
if(($videos->intro !== "-1" && $videos->intro !== null)||($videos->full !== "-1" && $videos->full !== null)):

	$full_video = $videos->full !== "-1" && $videos->full !== null ? 'data-fullvideo="' . JUri::root() . 'images/videos/' . $videos->full . '"' : '';
?>
	<div class="item-introvideo<?php echo $full_video != '' ? ' with-full-video' : '';?>" <?php echo $full_video;?> title="<?php echo JText::_('MOD_ARTICLES_HEAD_FRONT_PLAYVIDEO');?>">
	<?php
		if($videos->intro !== "-1" && $videos->intro !== null):
	?>
			<video preload="metadata" width="100%" loop muted>
				<source src="<?php echo JUri::root() . 'images/videos/' . $videos->intro;?>" type="video/<?php echo substr($videos->intro, strrpos($videos->intro,'.') + 1);?>" />
			</video>
	<?php
		endif;
	?>
	</div>
<?php
	endif;
?>
