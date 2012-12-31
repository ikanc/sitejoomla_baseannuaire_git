<?php

/**
 * @package     Extly.Addons
 * @subpackage  categoriesfilterapp - Categories Filter Addon allows to apply a categories filter to the search form.
 *
 * @author      Prieco S.A. <support@extly.com>
 * @copyright   Copyright (C) 2007 - 2012 Prieco, S.A. All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
 * @link        http://www.extly.com http://support.extly.com
 */
// No direct access
defined('_JEXEC') or die('Restricted access');

/**
 * ===================================================
 *  Based on SobiPro Notifications Application
 * ===================================================
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * Copyright (C) 2010 Sigsiu.NET (http://www.sigsiu.net). All rights reserved.
 * http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
*/
defined('SOBIPRO') || exit('Restricted access');

jimport('joomla.error.log');

/**
 * SPCategoriesFilterAppHelper class.
 *
 * @package     Prieco.Addons
 * @subpackage  categoriesfilterapp
 * @since       1.0
*/
abstract class SPCategoriesFilterAppHelper
{

	/**
	 * Trigger
	 *
	 * @param   mixed  &$data               the params
	 * @param   mixed  $categorymode        the params
	 * @param   mixed  $sorder              the params
	 * @param   mixed  $catlist             the params
	 * @param   mixed  $categorystartlevel  the params
	 *
	 * @return  the return value
	 *
	 * @since   1.0
	 */
	public static function Trigger(&$data, $categorymode, $sorder, $catlist, $categorystartlevel)
	{
		// $log = &JLog::getInstance('addon_categoriesfilterapp.log.' . date("Y-m-d") . '.php');
		// $log->addEntry(array('comment' => "*** Starting to log !" ));

		if ($catlist)
		{
			$catlist = json_decode($catlist);
			$catlist = implode(',', $catlist);
		}

		$jchainedlib = self::AddChainedLib();
		self::AddSearchLib();

		$moduleid = rand();
		$sectionid = Sobi::Section();

		if ($categorymode)
		{
			$result = SPCategoriesFilterAppCategoryHelper::getCategoryMode(
					$moduleid, $sectionid, $categorystartlevel, $categorymode, $sorder, $catlist, 0, $jchainedlib
			);
			if ($result['js'])
			{
				$js = $result['js'];
				$js = "
				var extSearchHelper{$moduleid};
				jQuery(document).ready(function() {
				extSearchHelper{$moduleid} = new ExtSearchHelperAddon(
				{$sectionid},
				'#spSearchForm',
				'#sid_listsp');

				jQuery('#top_button').bind('click', function() {
				extSearchHelper{$moduleid}.extractFormValues();
			});

			{$js}
			});";
			SPFactory::header()->addJsCode($js);
			}
		}
		$body = $result['body'];
		/*if ($error_message)
		 {
		$body .= $error_message;
		}*/
		if ($categorymode > 1)
		{
			$body .= "<input type=\"hidden\" id=\"sid_listsp\" name=\"sid_list\" value=\"{$catlist}\" />";
		}
		$data['spcategoriesfilterapp'] = '<!-- EXCLUDE EXT-SEARCH-MOD // BEGIN -->' . $body . '<!-- EXCLUDE EXT-SEARCH-MOD // END -->';
	}

	/**
	 * AddSearchLib
	 *
	 * @return  none
	 *
	 * @since   1.0
	 */
	public static function AddSearchLib()
	{
		$js = JFile::read('components/com_sobipro/opt/plugins/categoriesfilterapp/js/search.min.js');
		SPFactory::header()->addJsCode($js);
	}

	/**
	 * AddChainedLib
	 *
	 * @return  none
	 *
	 * @since   1.0
	 */
	public static function AddChainedLib()
	{
		$urlbase = JURI::root();
		$jchainedlib = null;
		$error_message = null;
		if (JFile::exists('modules/mod_sobiextsearch/js/jquery.chained.min.js'))
		{
			$jchainedlib = $urlbase . 'modules/mod_sobiextsearch/js/jquery.chained.min.js';
		}
		elseif (JFile::exists('modules/mod_spsearchincategories/js/search.min.js'))
		{
			$jchainedlib = $urlbase . 'modules/mod_sobiextsearch/js/jquery.chained.min.js';
		}
		elseif (JFile::exists('media/categoriesfilterapp/jquery.chained.min.js'))
		{
			$jchainedlib = $urlbase . 'modules/mod_sobiextsearch/js/jquery.chained.min.js';
		}
		elseif (JFile::exists('components/com_sobipro/opt/plugins/categoriesfilterapp/js/jquery.chained.min.js'))
		{
			$js = JFile::read('components/com_sobipro/opt/plugins/categoriesfilterapp/js/jquery.chained.min.js');
			SPFactory::header()->addJsCode($js);
		}
		else
		{
			$error_message = '<p class="error">Warning: jquery.chained.min.js not found!</p>';
		}
		if ($jchainedlib)
		{
			SPFactory::header()->addJsUrl($jchainedlib);
			return $jchainedlib;
		}
		return null;
	}

}
