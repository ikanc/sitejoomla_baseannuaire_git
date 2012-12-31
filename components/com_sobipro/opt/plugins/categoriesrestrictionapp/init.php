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

define('RMODE_1LEVEL', 0);
define('RMODE_RELATIONS', 1);
define('RMODE_PLUS', 2);

/**
 * SPCategoriesRestrictionApp class.
 *
 * @package     Prieco.Addons
 * @subpackage  categoriesfilterapp
 * @since       1.0
 */
class SPCategoriesRestrictionApp extends SPApplication
{

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	protected static $methods = array();

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	protected $triggers = array();

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	protected $categoryr_mode;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	protected $categoryr_forcedlist;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	protected $only_allowed;
	
	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	protected $debug_mode;	

	/**
	 * __construct
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		SPFactory::registry()->loadDBSection('categoriesrestrictionapp');
		$this->categoryr_mode = Sobi::Reg('categoriesrestrictionapp.categoryr_mode.value', 0);
		$this->categoryr_forcedlist = Sobi::Reg('categoriesrestrictionapp.categoryr_forcedlist.value', null);
		$this->only_allowed = Sobi::Reg('categoriesrestrictionapp.only_allowed.value', false);
		$this->debug_mode = Sobi::Reg('categoriesrestrictionapp.debug_mode.value', false);

		// Load std triggers
		$this->triggers = SPLoader::loadIniFile('etc.categoriesrestrictionapp');

		if (count($this->triggers))
		{
			foreach ($this->triggers as $subject => $triggers)
			{
				$subject = ucfirst($subject);
				if (count($triggers))
				{
					foreach ($triggers as $trigger => $label)
					{
						$trigger = explode('.', $trigger);
						self::$methods[] = $trigger[0];
					}
				}
			}
		}
		self::$methods = array_unique(self::$methods);
	}

	/**
	 * admMenu
	 *
	 * @param   mixed  &$links  the params
	 *
	 * @return  the return value
	 *
	 * @since   1.0
	 */
	public static function admMenu(&$links)
	{
		SPLang::load('SpApp.categoriesrestrictionapp');
		$links['CATEGORIESRESTRICTIONAPP.MENU_CATEGORIESRESTRICTIONAPP'] = 'categoriesrestrictionapp';
	}

	/**
	 * __call
	 *
	 * @param   mixed  $method  the params
	 * @param   mixed  $args    the params
	 * 
	 * @return  the return value
	 *
	 * @since   1.0
	 */
	public function __call($method, $args)
	{
		if (in_array($method, self::$methods))
		{
			SPLoader::loadClass('categoriesrestrictionapp.helper', false, 'application');
			SPCategoriesRestrictionAppHelper::Trigger($method, $args, $this->categoryr_mode, $this->categoryr_forcedlist, $this->only_allowed, $this->debug_mode);
		}
	}

	/**
	 * provide
	 *
	 * @param   mixed  $action  the params
	 * 
	 * @return  the return value
	 *
	 * @since   1.0
	 */
	public function provide($action)
	{
		return (in_array($action, self::$methods));
	}

}
