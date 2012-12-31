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

/**
 * ModSobiExtSearchHelper Helper class.
 *
 * @package     Prieco.Modules
 * @subpackage  mod_sobiextsearch
 * @since       1.0
 */
class ModSobiExtSearchHelper
{

	/**
	 * getDefaultItemid
	 *
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public static function getDefaultItemid()
	{
		$db = & JFactory::getDBO();
		$url = $db->quote("index.php?option=com_sobipro%");
		$type = $db->quote('component');

		$query = 'SELECT ' . $db->nameQuote('id') . ' FROM ' . $db->nameQuote('#__menu')
				. ' WHERE ' . $db->nameQuote('link') . ' like ' . $url . ' AND ' . $db->nameQuote('published') . '=' . $db->Quote(1) . ' '
				. 'AND ' . $db->nameQuote('type') . '=' . $db->Quote('component');

		$db->setQuery($query);
		$defaultId = $db->loadResult();
		return $defaultId;
	}

	/**
	 * _getForm
	 *
	 * @param   mixed  $sectionid  the params
	 * @param   mixed  $loader     the params
	 * @param   mixed  $mdebug     the params
	 * 
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public static function _getForm($sectionid, $loader, $mdebug)
	{
		$lang = JRequest::get('lang');
		if ($mdebug)
		{
			if (array_key_exists('lang', $lang))
			{
				echo 'DEBUG: Lang=' . $lang['lang'] . '<BR/>';
			}
		}

		/* Creating URL */
		$url = 'index.php?option=com_sobipro&amp;task=search&amp;sid=' . $sectionid;
		if (($lang) && (array_key_exists('lang', $lang)))
		{
			$url = $url . '&amp;lang=' . $lang['lang'];
		}
		/* End */

		if ($loader == 0)
		{

			// CURL
			$loadComponent = new LoadComponent;
			$result = $loadComponent->process($url);
		}
		else
		{ // Internal MVC Component Loader
			$document = & JFactory::getDocument();

			$backup_title = $document->getTitle();
			$backup_description = $document->getDescription();
			$backup_keywords = $document->getMetaData('keywords');

			$backup_option = JRequest::getVar('option', null);
			$backup_task = JRequest::getVar('task', null);
			$backup_sid = JRequest::getVar('sid', null);

			JRequest::setVar('option', 'com_sobipro');
			JRequest::setVar('task', 'search');
			JRequest::setVar('sid', $sectionid);
			JRequest::setVar('tmpl', 'component');
			JRequest::setVar('print', 1);

			$headerstuff = $document->getHeadData();

			ob_start();
			$path = JPATH_ROOT . DS . 'components' . DS . 'com_sobipro' . DS . 'sobipro.php';
			require $path;

			$result = ob_get_contents();
			ob_end_clean();

			JRequest::setVar('option', $backup_option);
			JRequest::setVar('task', $backup_task);
			JRequest::setVar('sid', $backup_sid);
			JRequest::setVar('tmpl', null);
			JRequest::setVar('print', null);

			$document->setTitle($backup_title);
			$document->setDescription($backup_description);
			$document->setMetaData('keywords', $backup_keywords);

			/*
			 * $data['title']       = $this->title;
			 * $data['description'] = $this->description;
			 * $data['metaTags']    = $this->_metaTags;
			 */

			$document->link = $headerstuff['link'];
			$document->_links = $headerstuff['links'];
			$document->_styleSheets = $headerstuff['styleSheets'];
			$document->_style = $headerstuff['style'];
			$document->_scripts = $headerstuff['scripts'];
			$document->_script = $headerstuff['script'];
			$document->_custom = $headerstuff['custom'];
		}

		if ($mdebug)
		{
			echo 'DEBUG: Result lenght=' . strlen($result) . '<BR/>';
		}

		if ($mdebug)
		{
			echo 'DEBUG: ***************<BR/>' . htmlspecialchars($result) . '<BR/>***************<BR/>';
		}

		$needleb = "<form action=\"index.php\" method=\"post\" id=\"spSearchForm";
		$positionb = strpos($result, $needleb);
		if ($positionb > 0)
		{
			$needlee = '</form>';
			$form = modSobiExtSearchHelper::_get_between($result, $positionb, $needlee);

			$needleb = '<!-- EXCLUDE EXT-SEARCH-MOD // BEGIN -->';
			$positionb = strpos($form, $needleb);
			if ($positionb > 0)
			{
				$needlee = '<!-- EXCLUDE EXT-SEARCH-MOD // END -->';
				$positione = strpos($form, $needlee);
				if ($positione > 0)
				{
					$form = substr($form, 0, $positionb) . substr($form, $positione + strlen($needlee));
				}
			}

			if ($mdebug)
			{
				echo 'DEBUG: |Form| ***************<BR/>' . htmlspecialchars($result) . '<BR/>***************<BR/>';
			}

			$pattern = "/(<input id=\"SPExOptBt\" [^\/]+\/>)/si";
			$form = preg_replace($pattern, '', $form);

			$pattern = "/(<div class=\"SobiPro componentheading\">.*?<\/div>)/si";
			$form = preg_replace($pattern, '', $form);

			$pattern = "/(<div class=\"spAlphaMenu\">.*?<\/div>)/si";
			$form = preg_replace($pattern, '', $form);

			$pattern = "/(<ul class=\"spTopMenu\">.*?<\/ul>)/si";
			$form = preg_replace($pattern, '', $form);

			$pattern = "/(<input type=\"hidden\" id=\"SP_ssid\" name=\"ssid\" value=\".*?\"\/>)/si";
			$form = preg_replace($pattern, '', $form);

			$pattern = "/(<input type=\"hidden\" id=\"SP_Itemid\" name=\"Itemid\" value=\"[0-9]+\"\/>.*?<\/div>)/si";
			$form = preg_replace($pattern, '', $form);

			$pattern = "/(<button [^>]+>.*?<\/button>)/si";
			$form = preg_replace($pattern, '', $form);

			$pattern = "/(<div id=\"osx-modal-title\">.*?<\/div>)/si";
			$form = preg_replace($pattern, '', $form);

			$pattern = "/(<div id=\"osx-close\">.*?<\/div>)/si";
			$form = preg_replace($pattern, '', $form);

			/* ID XT */
			$form = str_replace(' id="', ' id="XT', $form);
			$form = str_replace(' for="', ' for="XT', $form);

			// Warning: it can duplicate ID, but it works with previous SP versions
			$pattern = '/select name\=\"field_([a-z_]+)\"/';
			$replacement = 'select id="XTfield_$1" name="field_$1"';
			$form = preg_replace($pattern, $replacement, $form);

			/* Class XT */
			$form = str_replace(' class="SP', ' class="XTSP', $form);
			$form = str_replace(' class="sp', ' class="XTSP', $form);

			/* Styles */
			$form = str_replace('style="width: 350px;"', '', $form);
			$form = str_replace('style="float:left; width:150px;"', '', $form);
			$form = str_replace('style="overflow: scroll; height: 450px; "', '', $form);
			$form = str_replace('div style', 'div class="spspacer" style', $form);

			$pattern = "/( size=\"[0-9]+\")/si";
			$form = preg_replace($pattern, '', $form);

			return $form;
		}
		else
		{
			$needleb = "id=\"spSearchForm";
			$positionb = strpos($result, $needleb);
			if ($positionb > 0)
			{
				return "PARSE ERROR: SobiPro Component not loaded: <a href='$url'>$url</a>"
					. ($mdebug ? '<BR/><BR/><BR/>DEBUG: ***************<BR/>'
					. htmlspecialchars($result) . '<BR/>***************<BR/>' : '');
			}
			else
			{
				if ($loader == 0)
				{

					// CURL
					return "ERROR: EMPTY FORM.<BR/>Broken URL: <a href='$url'>$url</a>"
						. ($mdebug ? '<BR/><BR/><BR/>DEBUG: ***************<BR/>'
						. htmlspecialchars($result) . '<BR/>***************<BR/>' : '');
				}
				else
				{
					return "ERROR: Internal MVC Component Loader not working."
						. ($mdebug ? '<BR/><BR/><BR/>DEBUG: ***************<BR/>'
						. htmlspecialchars($result) . '<BR/>***************<BR/>' : '');
				}
			}
		}
		return null;
	}

	/**
	 * _get_between
	 *
	 * @param   mixed  $input  the params
	 * @param   mixed  $start  the params
	 * @param   mixed  $end    the params
	 * 
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public function _get_between($input, $start, $end)
	{
		$start = strpos($input, '>', $start) + 1;
		$substr = substr($input, $start, (strlen($input) - strpos($input, $end)) * (-1));
		return $substr;
	}

	/**
	 * getForm
	 *
	 * @param   mixed  $sectionid   the params
	 * @param   mixed  $searchword  the params
	 * @param   mixed  $button      the params
	 * @param   mixed  $loader      the params
	 * @param   mixed  $mdebug      the params
	 * 
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public function getForm($sectionid, $searchword, $button, $loader, $mdebug)
	{
		$form = modSobiExtSearchHelper::_getForm($sectionid, $loader, $mdebug);
		if ($form)
		{
			/* <input type="text" name="sp_search_for" value="search..." class="XTSPSearchBox" id="XTSPSearchBox" /> */
			$pattern = "/(<input ((\w+)=\"(\w+)\" )+name=\"sp_search_for\" value=\".+\" class=\"XTSPSearchBox\" id=\"XTSPSearchBox\" \/>)/si";
			$form = preg_replace($pattern, $searchword, $form);

			/* <input type="submit" name="search" value="Search" id="XTtop_button" /> */
			$pattern = "/(<input type=\"submit\" name=\"search\" value=\".+\" id=\"XTtop_button\" \/>)/si";
			$form = preg_replace($pattern, $button, $form);

			return $form;
		}

		return null;
	}

	/**
	 * enumerateClass
	 *
	 * @param   mixed  &$form  the params
	 * @param   mixed  $class  the params
	 * 
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public static function enumerateClass(&$form, $class)
	{
		$cells = explode('"' . $class . '"', $form);
		$form_new = '';
		$c = 1;
		foreach ($cells as $cell)
		{
			if ($c < count($cells))
			{
				$form_new .= $cell . '"' . $class . $c . '"';
				$c++;
			}
			else
			{
				$form_new .= $cell;
			}
		}
		return $form_new;
	}

	/**
	 * getSearchImage
	 *
	 * @param   mixed  $button_text  the params
	 * 
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public static function getSearchImage($button_text)
	{
		$img = JHtml::_('image', 'searchButton.gif', $button_text, null, true, true);
		return $img;
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

	/**
	 * fixComSearch
	 *
	 * @param   mixed  $output  the params
	 * 
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public static function fixComSearch($output)
	{
		$output = str_replace('<input type="hidden" id="XTSP_task" name="task" value="search.search"/>', '', $output);
		$output = str_replace('<input type="hidden" id="XTSP_option" name="option" value="com_sobipro"/>', '', $output);
		$output = str_replace('name="spsearchphrase"', 'name="searchphrase"', $output);

		$pattern = '/\<option value\=\"([a-z_]+)\"\>([^\<]+)\<\/option\>/';
		$replacement = '<option value="$2">$2</option>';
		$output = preg_replace($pattern, $replacement, $output);
		return $output;
	}

	/**
	 * generateScriptDeclaration
	 *
	 * @param   mixed  &$document                the params
	 * @param   mixed  &$toAddScriptDeclaration  the params
	 * 
	 * @return  the list
	 *
	 * @since   1.0
	 */
	public static function generateScriptDeclaration(&$document, &$toAddScriptDeclaration)
	{
		$finalAddScriptDeclaration = join("\n   ", $toAddScriptDeclaration);

		$document->addScriptDeclaration(
"jQuery(document).ready(function() {
	{$finalAddScriptDeclaration}
});"
		);
	}

}
