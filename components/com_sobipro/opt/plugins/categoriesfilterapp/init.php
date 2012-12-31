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

/**
 * SPCategoriesFilterApp class.
 *
 * @package     Extly.Addons
 * @subpackage  categoriesfilterapp
 * @since       1.0
 */
class SPCategoriesFilterApp extends SPApplication
{

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	protected static $methods = array('SearchDisplay');

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
	protected $categorymode;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	protected $sorder;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	protected $catlist;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	protected $categorystartlevel;

	/**
	 * __construct
	 *
	 * @since   1.0
	 */
	public function __construct()
	{
		SPFactory::registry()->loadDBSection('categoriesfilterapp');

		$this->categorymode = Sobi::Reg('categoriesfilterapp.categorymode.value', 0);
		$this->sorder = Sobi::Reg('categoriesfilterapp.sorder.value', 0);
		$this->catlist = Sobi::Reg('categoriesfilterapp.catlist.value', null);
		$this->categorystartlevel = Sobi::Reg('categoriesfilterapp.categorystartlevel.value', 1);

		// Load std triggers
		$this->triggers = SPLoader::loadIniFile('etc.categoriesfilterapp');

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
		SPLang::load('SpApp.categoriesfilterapp');
		$links['CATEGORIESFILTERAPP.MENU_CATEGORIESFILTERAPP'] = 'categoriesfilterapp';
	}

	/**
	 * SearchDisplay
	 *
	 * @param   mixed  &$data  the params
	 * 
	 * @return  the return value
	 *
	 * @since   1.0
	 */
	public function SearchDisplay(&$data)
	{
		SPLoader::loadClass('categoriesfilterapp.helper', false, 'application');
		SPLoader::loadClass('categoriesfilterapp.categoryhelper', false, 'application');
		SPCategoriesFilterAppHelper::Trigger($data, $this->categorymode, $this->sorder, $this->catlist, $this->categorystartlevel);
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
