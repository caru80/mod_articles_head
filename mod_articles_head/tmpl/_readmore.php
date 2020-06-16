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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

//
// Ausgabe des Weiterlesen-Link von einem Beitrag im Modul
//
if(isset($item->link)) :
?>
	<div class="readmore">
		<a itemprop="url" class="btn btn-primary more" href="<?php echo $item->link; ?>">
			<?php 
				if ($readmore = $item->alternative_readmore) :
					echo $readmore;
					if ($attribs->get('show_readmore_title', 0) != 0) :
						echo HTMLHelper::_('string.truncate', $item->title, $attribs->get('readmore_limit'));
					endif;
				elseif ($attribs->get('show_readmore_title', 0) == 0) :
					echo Text::sprintf('COM_CONTENT_READ_MORE_TITLE');
				else :
					echo Text::_('COM_CONTENT_READ_MORE');
					echo HTMLHelper::_('string.truncate', $item->title, $attribs->get('readmore_limit'));
				endif;
			?> 
			<i></i>
		</a>
	</div>
<?php
endif;	
?>