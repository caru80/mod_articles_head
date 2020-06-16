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

use Joomla\CMS\Language\Text;

$mod_readmore_url = $Helper->getModuleReadmoreUrl($params); // Modul-Weiterlesen URL
?>
<?php
//
// Template Ausgabe:
//
if ($mod_readmore_url != '') :
?>
	<div class="mod-intro-readmore">
		<a href="<?php echo $mod_readmore_url;?>" class="btn btn-primary more">
			<?php echo $params->get('module_readmore_label','') != '' ? $params->get('module_readmore_label','') : Text::_("MODARH_MODULE_READMORE_LABEL_FRONT");?>
			<i class="fas fa-chevron-right"></i>
		</a>
	</div>
<?php
endif;
?>