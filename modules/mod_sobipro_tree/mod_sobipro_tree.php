<?php

/**
 * @package     Extly.Modules
 * @subpackage  mod_sobipro_tree - SobiPro Tree of Categories
 * 
 * @author      Prieco S.A. <support@extly.com>
 * @copyright   Copyright (C) 2007 - 2012 Prieco, S.A. All rights reserved.
 * @license     http://http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL 
 * @link        http://www.prieco.com http://www.extly.com http://support.extly.com 
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
defined('SOBI_ROOT') || define('SOBI_ROOT', JPATH_ROOT);
defined('SOBI_PATH') || define('SOBI_PATH', SOBI_ROOT . DS . 'components' . DS . 'com_sobipro');

require_once SOBI_PATH . '/lib/sobi.php';
Sobi::Init(SOBI_ROOT, JFactory::getConfig()->getValue('config.language'));
SPLoader::loadClass('mlo.input');
SPFactory::config()->set('live_site', JURI::root());

// Include the syndicate functions only once
require_once dirname(__FILE__) . DS . 'helper.php';
require_once dirname(__FILE__) . DS . 'stats.php';

$parentid = intval($params->get('parentid', 1));
$categorymode = intval($params->get('categorymode', 1));
$sorder = intval($params->get('sorder', 0));
$scounter = intval($params->get('scounter', 0));
$hide_empty = intval($params->get('hide_empty', false));

// To force a global itemid
$scitemid = $params->get('scitemid', null);
$categorystartlevel = intval($params->get('categorystartlevel', 1));
$tree_colapsed = intval($params->get('tree_colapsed', 1));
$tree_persist = intval($params->get('tree_persist', 1));
$jqueryjs = intval($params->get('jqueryjs', 1));
$count = intval($params->get('count', 100));

$moduleclass_sfx = $params->get('moduleclass_sfx', null);
$debug = intval($params->get('debug', 0));

/* ---------------------------------------------------------------- */
$require_stats = (($hide_empty) || ($scounter == 2));
$params->set('require_stats', $require_stats);

$defaultItemid = ModSobiproTreeHelper::getDefaultItemid($params->get('parentid', null));

// Or, specific Itemid per category
$subItemsid = ModSobiproTreeHelper::getSubItemsid($defaultItemid);

// Current Url, SID
$raw_sid_full = JRequest::getVar('sid');
//LGW : si le pid (category) est donné, il prend le pas sur le sid (entry)
$raw_pid_full = JRequest::getVar('pid');
if ($raw_pid_full!='') $raw_sid_full = $raw_pid_full;

$actual_sid_full = explode(":", $raw_sid_full);
//$actual_sid = (count($actual_sid_full) == 2 ? $actual_sid_full[0] : null);
$actual_sid = $actual_sid_full[0];

if ($require_stats)
{
	if (ModSobiproTreeStatsHelper::init())
	{
		$totals = ModSobiproTreeStatsHelper::calculateTotalEntries($parentid, $debug);
	}
	else
	{
		$require_stats = false;
		$hide_empty = false;
		$scounter = 0;
	}
}

switch ($tree_persist)
{
	case 1:
		$save_tree = '"cookie"';
		break;
	case 2:
		$save_tree = '"location"';
		break;
	default;
		$save_tree = 'null';
		break;
}

$doc = & JFactory::getDocument();
/*
if ($jqueryjs)
{
	$doc->addScript('components/com_sobipro/lib/js/jquery.js');
}

// $doc->addScript('modules/mod_sobipro_tree/assets/jquery.treeviewboth.js');

$moduleid = $module->id;
$base_url = JURI::root();
$doc->addScriptDeclaration(
'
window.addEvent(\'domready\', function() {
    if (jQuery().treeview) {
        jQuery("#sptreebrowser' . $moduleid . '").treeview({
            collapsed: ' . ($tree_colapsed ? 'true' : 'false') . ',
            animated: "fast",
            persist: ' . $save_tree . '
        });	
    } else {
        jQuery.getScript("' . $base_url . 'modules/mod_sobipro_tree/assets/jquery.treeviewboth.js", function () {
            jQuery("#sptreebrowser' . $moduleid . '").treeview({
                collapsed: ' . ($tree_colapsed ? 'true' : 'false') . ',
                animated: "fast",
                persist: ' . $save_tree . '
            });
        });
    }
});
'
);*/

/*
jQuery(document).ready(function () {
	if (typeof jQuery.cookie === 'undefined') {
		jQuery.getScript("modules/mod_sobipro_tree/assets/jquery.treeviewboth.js", function () {
			jQuery("#sptreebrowser").treeview({
				collapsed: true,
				animated: "fast",
				persist: true
			});
		});
	} else {
		jQuery("#sptreebrowser").treeview({
			collapsed: true,
			animated: "fast",
			persist: true
		});
}
*/

/*$doc->addStyleSheet("modules/mod_sobipro_tree/assets/jquery.treeview.css");*/

require JModuleHelper::getLayoutPath('mod_sobipro_tree', 'default');
