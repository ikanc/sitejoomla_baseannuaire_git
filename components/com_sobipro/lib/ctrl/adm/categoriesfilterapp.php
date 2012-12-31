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

SPLoader::loadController('config', true);

/**
 * SPCategoriesFilterAppCtrl class.
 *
 * @package     Prieco.Addons
 * @subpackage  categoriesfilterapp
 * @since       1.0
 */
class SPCategoriesFilterAppCtrl extends SPConfigAdmCtrl
{

	/**
	 * @var string
	 */
	protected $_type = 'categoriesfilterapp';

	/**
	 * @var string
	 */
	protected $_defTask = 'config';

	/**
	 * execute
	 *
	 * @return  nil
	 *
	 * @since   1.0
	 */
	public function execute()
	{
		$this->_task = strlen($this->_task) ? $this->_task : $this->_defTask;
		SPLang::load('SpApp.categoriesfilterapp');
		switch ($this->_task)
		{
			case 'config':
				$this->screen();
				Sobi::ReturnPoint();
				break;
			case 'save':
				$this->save();
				break;
			default:
				Sobi::Error('SPCategoriesFilterAppCtrl', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__);
				break;
		}
	}

	/**
	 * screen
	 *
	 * @return  nil
	 *
	 * @since   1.0
	 */
	private function screen()
	{
		SPFactory::registry()->loadDBSection('categoriesfilterapp');
		$view = $this->getView('categoriesfilterapp');
		if (SPFs::exists(implode(DS, array(SOBI_PATH, 'opt', 'plugins', 'categoriesfilterapp', 'description_' . Sobi::Lang() . '.html'))))
		{
			$c = SPFs::read(implode(DS, array(SOBI_PATH, 'opt', 'plugins', 'categoriesfilterapp', 'description_' . Sobi::Lang() . '.html')));
		}
		else
		{
			$c = SPFs::read(implode(DS, array(SOBI_PATH, 'opt', 'plugins', 'categoriesfilterapp', 'description_en-GB.html')));
		}
		$view->assign($c, 'description');

		$view->assign(Sobi::Reg('categoriesfilterapp.categorymode.value'), 'categorymode');
		$view->assign(Sobi::Reg('categoriesfilterapp.sorder.value'), 'sorder');
		$view->assign(Sobi::Reg('categoriesfilterapp.catlist.value'), 'catlist');
		$view->assign(Sobi::Reg('categoriesfilterapp.categorystartlevel.value'), 'categorystartlevel');

		$view->loadConfig('extensions.categoriesfilterapp');
		$view->setTemplate('extensions.categoriesfilterapp');
		$view->display();
	}

	/**
	 * save
	 *
	 * @return  nil
	 *
	 * @since   1.0
	 */
	protected function save()
	{
		$value_categorymode = SPRequest::string('categorymode');
		$value_sorder = SPRequest::string('sorder');
		$value_catlist = json_encode(SPRequest::arr('catlist'));
		$value_categorystartlevel = SPRequest::string('categorystartlevel');

		SPFactory::registry()->saveDBSection(
			array(
				array('key' => 'categorymode', 'value' => $value_categorymode),
				array('key' => 'sorder', 'value' => $value_sorder),
				array('key' => 'catlist', 'value' => $value_catlist),
				array('key' => 'categorystartlevel', 'value' => $value_categorystartlevel)), $this->_type
		);
		Sobi::Redirect(SPMainFrame::getBack(), Sobi::Txt('MSG.ALL_CHANGES_SAVED'));
	}

}
