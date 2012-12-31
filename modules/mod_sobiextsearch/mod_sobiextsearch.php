<?php

/**
 * @package     Prieco.Modules
 * @subpackage  mod_sobiextsearch - This module will load the "Extended Search" as a module.
 * 
 * @author      Prieco S.A. <support@extly.com>
 * @copyright   Copyright (C) 2010 - 2012 Prieco, S.A. All rights reserved.
 * @license     http://http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL 
 * @link        http://www.prieco.com http://www.extly.com http://support.extly.com 
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

defined('DS') || define('DS', DIRECTORY_SEPARATOR);
define('SOBI_ROOT', JPATH_ROOT);
define('SOBI_PATH', SOBI_ROOT . DS . 'components' . DS . 'com_sobipro');

// Include the syndicate functions only once
require_once dirname(__FILE__) . DS . 'helper.php';
require_once dirname(__FILE__) . DS . 'helper_category.php';
require_once dirname(__FILE__) . DS . 'load_component.php';

if ($params->get('opensearch', 1))
{
	$doc = JFactory::getDocument();
	$app = JFactory::getApplication();

	$ostitle = $params->get('opensearch_title', JText::_('MOD_SOBIEXTSEARCH_SEARCHBUTTON_TEXT') . ' ' . $app->getCfg('sitename'));
	$doc->addHeadLink(
				JURI::getInstance()->toString(array('scheme', 'host', 'port')) . JRoute::_('&option=com_search&format=opensearch'),
				'search',
				'rel',
				array('title' => $ostitle, 'type' => 'application/opensearchdescription+xml')
			);
}

// $upper_limit = $lang->getUpperLimitSearchWord(); Only J 1.6/1.7

$urlbase = JURI::root();

/* * * Parms Begin ** */
$smode = ($params->get('smode', 0));
$sectionid = ($params->get('sectionid', 1));
$categorymode = ($params->get('categorymode', 0));
$categorystartlevel = ($params->get('categorystartlevel', 0));
$sorder = ($params->get('sorder', 0));
$catlist = $params->get('catlist', null);

$width = ($params->get('width', 20));
$text = htmlspecialchars($params->get('text', JText::_('MOD_SOBIEXTSEARCH_SEARCHBOX_TEXT')));
$button = $params->get('button', 1);
$imagebutton = $params->get('imagebutton', '');
$button_text = htmlspecialchars($params->get('button_text', JText::_('MOD_SOBIEXTSEARCH_SEARCHBUTTON_TEXT')));
$set_itemid = ($params->get('set_itemid', 0));
$autocomplete = ($params->get('autocomplete', 1));

// Advanced
$loader = ($params->get('loader', 0));
// $allow_empty = ($params->get('allow_empty', 0));
$jqueryjs = ($params->get('jqueryjs', 1));
$mj_rs = ($params->get('mj_rs', 0));
$gsensorjs = ($params->get('gsensorjs', 0));
$mdebug = ($params->get('mdebug', 0));
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

/* * * Parms End ** */

if ($mdebug)
{
	$msg_catlist = print_r($catlist, true);
	echo "Validated catlist: {$msg_catlist}.</br>";
}

$maxlength = $width > 20 ? $width : 20;

$mitemid = $set_itemid > 0 ? $set_itemid : JRequest::getInt('Itemid');
if ($imagebutton)
{
	$img = modSobiExtSearchHelper::getSearchImage($button_text);
}

$document = & JFactory::getDocument();

$toAddScriptDeclaration = array();

$jqueryuilib = $urlbase . 'components/com_sobipro/lib/js/jquery-ui.js';
if ((($autocomplete || $categorymode)) && ($jqueryjs))
{
	require_once SOBI_PATH . '/lib/sobi.php';
	Sobi::Init(SOBI_ROOT, JFactory::getConfig()->getValue('config.language'));
	SPLoader::loadClass('mlo.input');
	SPFactory::config()->set('live_site', JURI::root());
	$head = SPFactory::header();
	$head->addJsFile('sobipro');
	$head->send();

	$document->addStyleSheet($urlbase . 'media/sobipro/css/jquery-ui/smoothness/smoothness.css');
	$document->addScript($urlbase . 'components/com_sobipro/lib/js/jquery.js');
	$document->addScript($jqueryuilib);
	$document->addScriptDeclaration('jQuery.noConflict();');
}

if ($mdebug)
{
	$document->addScript($urlbase . 'modules/mod_sobiextsearch/js/modsobiextsearch.js');
}
else
{
	$document->addScript($urlbase . 'modules/mod_sobiextsearch/js/modsobiextsearch.min.js');
}

$jchainedlib = 'not-defined';
if ($categorymode > 1)
{
	if ($mdebug)
	{
		$jchainedlib = $urlbase . 'modules/mod_sobiextsearch/js/jquery.chained.js';
	}
	else
	{
		$jchainedlib = $urlbase . 'modules/mod_sobiextsearch/js/jquery.chained.min.js';
	}
	$document->addScript($jchainedlib);
}

if ($mj_rs)
{
	if ($mdebug)
	{
		$document->addScript($urlbase . 'modules/mod_sobiextsearch/js/mjrs.js');
	}
	else
	{
		$document->addScript($urlbase . 'modules/mod_sobiextsearch/js/mjrs.min.js');
	}

	$document->addScriptDeclaration("var mjRsHelper;");
	$toAddScriptDeclaration[] = "mjRsHelper = new MjRsHelper();";
}

if ($gsensorjs)
{
	$document->addScript('http://maps.googleapis.com/maps/api/js?sensor=true&libraries=places');
}

$moduleid = $module->id;
$searchformid = ($smode ? 'XTjooSearchForm' . $moduleid : 'XTspSearchForm' . $moduleid );
$addField = ($smode ? 'ExtSearchHelper.prototype.addField' : 'null' );
$fillSearchBox = ($smode ? 'ExtSearchHelper.prototype.fillSearchBox' : 'null' );

$document->addScriptDeclaration("var extSearchHelper{$moduleid};");
$searchfieldid = ($smode ? '#modsearchsearchword' : '#XTSPSearchBox' ) . $moduleid;
$toAddScriptDeclaration[] = "extSearchHelper{$moduleid} = new ExtSearchHelper(
		{$sectionid},
		'#{$searchformid}',
		'{$searchfieldid}',
		'#sid_list{$moduleid}',
		'{$text}',
		{$addField},
		{$fillSearchBox}
	);";

if ($autocomplete)
{
	$toAddScriptDeclaration[] = "extSearchHelper{$moduleid}.bind('{$jqueryuilib}');";
}

$layout = ($smode ? 'com_search' : 'com_sobipro' );
require JModuleHelper::getLayoutPath('mod_sobiextsearch', $layout);
