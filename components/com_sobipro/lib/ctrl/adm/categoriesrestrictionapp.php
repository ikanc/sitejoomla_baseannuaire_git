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

SPLoader::loadController('config', true);

/**
 * SPCategoriesRestrictionAppCtrl class.
 *
 * @package     Prieco.Addons
 * @subpackage  categoriesrestrictionapp
 * @since       1.0
 */
class SPCategoriesRestrictionAppCtrl extends SPConfigAdmCtrl
{

	/**
	 * @var string
	 */
	protected $_type = 'categoriesrestrictionapp';

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
		SPLang::load('SpApp.categoriesrestrictionapp');
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
				Sobi::Error('SPCategoriesRestrictionAppCtrl', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__);
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
		SPFactory::registry()->loadDBSection('categoriesrestrictionapp');
		$view = $this->getView('categoriesrestrictionapp');
		if (SPFs::exists(implode(DS, array(SOBI_PATH, 'opt', 'plugins', 'categoriesrestrictionapp', 'description_' . Sobi::Lang() . '.html'))))
		{
			$c = SPFs::read(implode(DS, array(SOBI_PATH, 'opt', 'plugins', 'categoriesrestrictionapp', 'description_' . Sobi::Lang() . '.html')));
		}
		else
		{
			$c = SPFs::read(implode(DS, array(SOBI_PATH, 'opt', 'plugins', 'categoriesrestrictionapp', 'description_en-GB.html')));
		}
		$view->assign($c, 'description');
		$view->assign(Sobi::Reg('categoriesrestrictionapp.categoryr_mode.value'), 'categoryr_mode');
		$view->assign(Sobi::Reg('categoriesrestrictionapp.categoryr_forcedlist.value'), 'categoryr_forcedlist');
		$view->assign(Sobi::Reg('categoriesrestrictionapp.only_allowed.value'), 'only_allowed');
		$view->assign(Sobi::Reg('categoriesrestrictionapp.debug_mode.value'), 'debug_mode');

		$view->loadConfig('extensions.categoriesrestrictionapp');
		$view->setTemplate('extensions.categoriesrestrictionapp');
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
		$valuerm = SPRequest::string('categoryr_mode');
		$valuefl = json_encode(SPRequest::arr('categoryr_forcedlist'));
		$valueoa = SPRequest::string('only_allowed');
		$valuedm = SPRequest::string('debug_mode');

		SPFactory::registry()->saveDBSection(
				array(
			array('key' => 'categoryr_mode', 'value' => $valuerm),
			array('key' => 'categoryr_forcedlist', 'value' => $valuefl),
			array('key' => 'only_allowed', 'value' => $valueoa),
			array('key' => 'debug_mode', 'value' => $valuedm)
					), $this->_type
				);
		Sobi::Redirect(SPMainFrame::getBack(), Sobi::Txt('MSG.ALL_CHANGES_SAVED'));
	}

}
