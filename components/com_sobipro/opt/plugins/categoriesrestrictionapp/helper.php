<?php
/**
 * @package     Prieco.Addons
 * @subpackage  categoriesrestrictionapp - Categories Restriction Addon allows to apply a restriction (sid_list) to the search results list.
 * 
 * @author      Prieco S.A. <support@extly.com>
 * @copyright   Copyright (C) 2007 - 2012 Prieco, S.A. All rights reserved.
 * @license     http://http://www.gnu.org/licenses/gpl-3.0.html GNU/GPL 
 * @link        http://www.prieco.com http://www.extly.com http://support.extly.com 
 */
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
 * SPCategoriesRestrictionAppHelper class.
 *
 * @package     Prieco.Addons
 * @subpackage  categoriesfilterapp
 * @since       1.0
 */
abstract class SPCategoriesRestrictionAppHelper
{

	/**
	 * Trigger
	 *
	 * @param   mixed  $action                the params
	 * @param   mixed  $args                  the params
	 * @param   mixed  $categoryr_mode        the params
	 * @param   mixed  $categoryr_forcedlist  the params
	 * @param   mixed  $only_allowed          the params
	 *
	 * @return  the return value
	 *
	 * @since   1.0
	 */
	public static function Trigger($action, $args, $categoryr_mode, $categoryr_forcedlist, $only_allowed, $debug_mode = 0)
	{
		$categoryr_forcedlist = json_decode($categoryr_forcedlist);		
		if (count($categoryr_forcedlist) == 1)
		{
			$v = $categoryr_forcedlist[0];
			if (empty($v))
			{
				$categoryr_forcedlist = null;
			}
		}
		if ($debug_mode)
		{
			$log = &JLog::getInstance('addon_categoriesrestrictionapp.log.' . date("Y-m-d") . '.php');
		}

		if (count($args[0]) == 0)
		{
			return;
		}
		$resultsq = join(',', $args[0]);

		if ($debug_mode)
		{
			$log->addEntry(array('comment' => "*** Starting to log !" ));
			$log->addEntry(array('comment' => "*** action {$action}, results [{$resultsq}], categoryr_mode {$categoryr_mode}, categoryr_forcedlist {$categoryr_forcedlist}, only_allowed {$only_allowed}" ));
		}

		$sid_list = JRequest::getVar('sid_list', null);
		if ($sid_list == '')
		{
			$sid_list = null;
		}

		if ($sid_list)
		{
			$sid_list = preg_replace('/[^,0-9]/', '', $sid_list);
			$sid_list = explode(',', $sid_list);
		}
		
		if ($debug_mode)
		{
			$log->addEntry(array('comment' => "*** sid_list " . print_r($sid_list, true)));
		}

		// Checking the restrictions to be applied
		if ($debug_mode)
		{
			$log->addEntry(array('comment' => "*** case 1 " . count($sid_list) . '-' . count($categoryr_forcedlist) ));
		}

		if ((count($sid_list) > 0) && (count($categoryr_forcedlist) > 0))
		{
			$sid_list = array_unique(array_merge($sid_list, $categoryr_forcedlist));
		}
		else
		{
			if ($debug_mode)
			{
				$log->addEntry(array('comment' => "*** case 2 " . count($categoryr_forcedlist) ));
			}

			if (count($categoryr_forcedlist) > 0)
			{
				$sid_list = $categoryr_forcedlist;
			}
			else
			{
				if ($debug_mode)
				{				
					$log->addEntry(array('comment' => "*** case 3 " . count($sid_list) ));
				}

				if (count($sid_list) == 0)
				{
					if ($only_allowed)
					{
						$args[0] = null;
					}
					if ($debug_mode)
					{
						$log->addEntry(array('comment' => "*** case 4 " . print_r($args[0], true) ));
					}

					return;
				}
			}
		}

		$sid_listq = join(',', $sid_list);
		
		if ($debug_mode)
		{
			$log->addEntry(array('comment' => "*** sid_listq {$sid_listq}" ));
		}

		$_db = JFactory::getDbo();
		switch ($categoryr_mode)
		{
			case RMODE_RELATIONS: // Based on sobipro_relations
				$query = 'SELECT o.id FROM `#__sobipro_object` o, 
					`#__sobipro_relations` r 
					WHERE o.oType=\'entry\' 
					AND o.id = r.id  AND r.pid IN (' . $sid_listq . ') AND o.id IN (' . $resultsq . ');';

				if ($debug_mode)
				{				
					$log->addEntry(array('comment' => "*** query {$query}" ));
				}

				$_db->setQuery($query);
				$results = array_unique($_db->loadResultArray());
				break;

			// N-Categories case, requires Search Plugin Plus+
			case RMODE_PLUS:
				$sid_listq = $sid_list;
				array_walk($sid_listq, create_function('&$sid', '$sid = "t.path LIKE \'%-{$sid}-%\'";'));
				$sid_listq = join(' OR ', $sid_listq);

				$query = 'SELECT o.id FROM 
					`#__sobipro_object` o, 
					`#__prsobiproplus_tree` t 
					WHERE o.oType=\'entry\' AND o.id = t.id  AND  (' . $sid_listq . ') AND o.id IN (' . $resultsq . ');';

				if ($debug_mode)
				{				
					$log->addEntry(array('comment' => "*** query {$query}" ));
				}

				$_db->setQuery($query);
				$results = array_unique($_db->loadResultArray());
				break;

			// Simple case, 1-Level Category
			default:
				$query = 'SELECT o.id FROM `#__sobipro_object` o WHERE o.oType=\'entry\' AND parent IN (' . $sid_listq . ') AND o.id IN (' . $resultsq . ');';

				if ($debug_mode)
				{				
					$log->addEntry(array('comment' => "*** query {$query}" ));
				}

				$_db->setQuery($query);
				$results = $_db->loadResultArray();
				break;
		}

		if ($debug_mode)
		{		
			$log->addEntry(array('comment' => "*** results " . print_r($results, true) ));
		}

		$args[0] = $results;
	}

}
