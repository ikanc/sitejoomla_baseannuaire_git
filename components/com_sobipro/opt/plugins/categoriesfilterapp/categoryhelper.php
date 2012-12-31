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
 * ModCategoryBrowserHelper Helper class.
 *
 * @package     Extly.Modules
 * @subpackage  mod_sobiextsearch
 * @since       1.0
 */
class SPCategoriesFilterAppCategoryHelper
{

	/**
	 * getCategoryMode
	 *
	 * @param   mixed  $moduleid            the params
	 * @param   mixed  $section             the params
	 * @param   mixed  $categorystartlevel  the params
	 * @param   mixed  $categorymode        the params
	 * @param   mixed  $sorder              the params
	 * @param   mixed  $catlist             the params
	 * @param   mixed  $mdebug              the params
	 * @param   mixed  $jchainedlib         the params
	 *
	 * @return  the output
	 *
	 * @since   1.0
	 */
	public static function getCategoryMode($moduleid, $section, $categorystartlevel, $categorymode, $sorder, $catlist, $mdebug, $jchainedlib)
	{
		$l2Parents = array();
		$l2Parents[] = $section;
		$chained_parent = null;
		$chained_js = '';

		$sid_list = null;
		if (($catlist) && (is_array($catlist)) && (count($catlist) > 0))
		{
			$sid_list = implode(',', $catlist);
		}
		else
		{
			$sid_list = $catlist;
		}

		$output = array();
		for ($i = 1; ($i <= $categorymode); $i++)
		{
			$list = self::_getList($l2Parents, $sorder, $sid_list, $mdebug);
			if (is_array($list))
			{
				$l2Parents = array_keys($list);
			}

			if ($i >= $categorystartlevel)
			{
				$select = self::_getSelect($moduleid, $categorymode, $i, $list);
				if ($select)
				{
					if ($chained_parent)
					{
						$id = $select['id'];
						$chained_js .= "jQuery('#{$id}').chained('#{$chained_parent}');";
					}
					$output[] = $select['body'];
					$chained_parent = $select['id'];
				}
			}
		}

		$final_chained_js = null;
		if ($chained_js)
		{
			$final_chained_js = '
    if (typeof jQuery.fn.chained === \'function\') {
        ' . $chained_js . '
    } else {
        jQuery.getScript(\'' . $jchainedlib . '\', function () {
            ' . $chained_js . '
        });
    };';
		}

		return array('js' => $final_chained_js, 'body' => join('', $output));
	}

	/**
	 * _getList
	 *
	 * @param   mixed  $parent    the params
	 * @param   mixed  $sorder    the params
	 * @param   mixed  $sid_list  the params
	 * @param   mixed  $mdebug    the params
	 * 
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public static function _getList($parent, $sorder, $sid_list, $mdebug)
	{
		// Multimode
		$isMultimode = Sobi::Cfg('lang.multimode', false);
		$lang = JFactory::getLanguage();
		$current_lang = $lang->getTag();

		$lparent = join(',', $parent);
		$db = & JFactory::getDBO();
		$qorder = '';
		if ($sorder == 1)
		{
			$qorder = ' ORDER BY c.name';
		}
		elseif ($sorder == 2)
		{
			$qorder = ' ORDER BY c.id';
		}
		elseif ($sorder == 3)
		{
			$qorder = ' ORDER BY c.counter desc';
		}
		elseif ($sorder == 4)
		{
			$qorder = ' ORDER BY c.nid';
		}
		elseif ($sorder == 5)
			$qorder = ' ORDER BY c.position, c.name';

		if ($isMultimode)
		{
			$query = 'SELECT c.id AS id, IF(l.sValue IS NULL, c.name, l.sValue) AS name, c.nid AS alias, c.counter AS counter, parent as cid' .
					' FROM #__sobipro_object AS c ' .
					' JOIN #__sobipro_category AS cc ON c.id = cc.id ' .
					' LEFT OUTER JOIN #__sobipro_language l ON l.sKey = ' . $db->Quote('name') . ' AND l.id = c.id AND l.language = ' . $db->Quote($current_lang) .
					' WHERE c.state = 1 AND c.oType=' . $db->Quote('category');
		}
		else
		{
			$query = 'SELECT c.id AS id, c.name AS name, c.nid AS alias, c.counter AS counter, c.parent as cid' .
					' FROM #__sobipro_object AS c ' .
					' LEFT JOIN #__sobipro_category AS cc ON c.id = cc.id ' .
					' WHERE c.state = 1 AND c.oType=' . $db->Quote('category');
		}

		$query = $query . ' AND parent IN (' . $lparent . ')';
		if ($sid_list)
		{
			$query = $query . ' AND c.id IN (' . $sid_list . ')';
		}
		$query = $query . $qorder;

		if ($mdebug)
		{
			echo 'QUERY:' . $query . '</br>';
		}

		$db->setQuery($query);
		$rows = $db->loadObjectList('id');

		if ($mdebug)
		{
			echo "Rows parent ({$parent}):" . count($rows) . '</br>';
		}

		return $rows;
	}

	/**
	 * _getSelect
	 *
	 * @param   mixed  $moduleid      the params
	 * @param   mixed  $categorymode  the params
	 * @param   mixed  $level         the params
	 * @param   mixed  &$categories   the params
	 * 
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public static function _getSelect($moduleid, $categorymode, $level, &$categories)
	{
		if (!(($categories) && (count($categories) > 1)))
		{
			return null;
		}

		$sid_list = JRequest::getVar('sid_list', null);
		if ((!$sid_list) || (empty($sid_list)))
		{
			$sid_list = null;
			$selected_catlist = null;
		}
		if ($sid_list)
		{
			$selected_catlist = explode(',', ModCategoryBrowserHelper::_cleanListOfNumerics($sid_list));
		}

		$i = 0;
		$output = array();
		if ($categorymode == 1)
		{
			$objname = 'sid_list';
			$objid = $objname;
		}
		else
		{
			$objid = "extparent_{$moduleid}{$level}";
			$objname = "to_sid_list_{$moduleid}{$level}";
		}
		$first = "<select class='sid_list{$level}' name='{$objname}' id='{$objid}' style='margin-top:4px;'>";
		$selected = false;
		foreach ($categories as $category)
		{
			$catid = $category->cid;
			$myId = $category->id;
			if ((!$selected) && ($selected_catlist) && (in_array($myId, $selected_catlist)))
			{
				$selected = true;
				$selectedhtml = 'selected="selected"';
			}
			else
			{
				$selectedhtml = '';
			}

			// $category_name = htmlentities($category->name);
			$category_name = htmlspecialchars($category->name);

			$output[] = "<option style=\"\" value=\"spc{$myId}\" class=\"spc{$catid}\" {$selectedhtml}>{$category_name}</option>";
			$i++;
		}
		$textselect = JText::_('JOPTION_SELECT_CATEGORY');
		if (!$selected)
		{
			$toselect = "<option style=\"\" value=\"\" selected=\"selected\">{$textselect}</option>";
		}
		else
		{
			$toselect = "<option style=\"\" value=\"\">{$textselect}</option>";
		}
		array_unshift($output, $first, $toselect);

		$output[] = '</select>';

		$select = array('id' => $objid, 'body' => join('', $output));
		return $select;
	}

	/**
	 * _cleanListOfNumerics
	 *
	 * @param   mixed  $listOfNumerics  the params
	 * 
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public static function _cleanListOfNumerics($listOfNumerics)
	{
		return preg_replace('/[^,0-9]/', '', $listOfNumerics);
	}

}
