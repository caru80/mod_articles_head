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

use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Language\Text;

// -- Videos
$videos = (object)array("intro" => $attribs->get('xfields_teaser_video'), "full" => $attribs->get('xfields_full_video'));

/**
	Achtung: Joomla Dateiauswahlfelder haben einen Standardwert vom Typ string mit dem Wert -1, wenn nichts ausgewählt wurde. Und den Typ null, wenn noch nie etwas ausgewählt wurde.
*/
if ( ($videos->intro !== "-1" && $videos->intro !== null)
	|| ($videos->full !== "-1" && $videos->full !== null) ) :

	$full_video_url = $videos->full !== "-1" && $videos->full !== null ? 'data-fullvideo="' . Uri::root() . 'images/videos/' . $videos->full . '"' : '';
?>
	<div class="item-introvideo<?php echo $full_video_url != '' ? ' with-full-video' : '';?>" <?php echo $full_video_url;?> title="<?php echo Text::_('MODARH_FRONT_PLAYVIDEO');?>">
	<?php
		if ($full_video_url !== '') :
	?>	
		<div class="introvideo-play">
			<div class="play-icon">
				<svg class="play-icon-svg" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 510 510" preserveAspectRatio="xMinYMin meet">
					<g id="play-circle-outline">
						<path d="M204,369.75L357,255L204,140.25V369.75z M255,0C114.75,0,0,114.75,0,255s114.75,255,255,255s255-114.75,255-255
							S395.25,0,255,0z M255,459c-112.2,0-204-91.8-204-204S142.8,51,255,51s204,91.8,204,204S367.2,459,255,459z"/>
					</g>
				</svg>
			</div>
			<div class="play-text"><?php echo Text::_('MODARH_FRONT_PLAYVIDEO');?></div>
		</div>
	<?php
		endif;
	?>
	<?php
		if($videos->intro !== "-1" && $videos->intro !== null) :
	?>
			<video preload="metadata" width="100%" loop muted>
				<source src="<?php echo Uri::root() . 'images/videos/' . $videos->intro;?>" type="video/<?php echo substr($videos->intro, strrpos($videos->intro,'.') + 1);?>" />
			</video>
	<?php
		endif;
	?>
	</div>
<?php
	endif;
?>
