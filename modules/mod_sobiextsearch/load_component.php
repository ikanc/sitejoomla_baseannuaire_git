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

/*
  Based on plugin_component.php,v 1.10 2011/02/16 12:34:11 Reumer.net
  http://joomlacode.org/gf/project/include_comp/frs/
 */

/**
 * LoadComponent Helper class.
 *
 * @package     Prieco.Modules
 * @subpackage  mod_sobiextsearch
 * @since       1.0
 */
class LoadComponent
{

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $regex;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $document;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $doctype;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $ignore_scripts;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $ignore_styles;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $method;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $closesession;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $ignoresef;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $cbreplace;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $replprint;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $repltmpl;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $url;

	/**
	 * A variable object.
	 *
	 * @var    Variable
	 * @since  1.0
	 */
	public $base;

	/**
	 * process
	 *
	 * @param   mixed  $ignore_script  the params
	 * @param   mixed  $ignore_style   the params
	 * @param   mixed  $method         the params
	 * @param   mixed  $closesession   the params
	 * @param   mixed  $ignoresef      the params
	 * @param   mixed  $cbreplace      the params
	 * @param   mixed  $replprint      the params
	 * @param   mixed  $repltmpl       the params
	 * 
	 * @since   1.0
	 */
	public function __construct(
		$ignore_script = '',
		$ignore_style = '',
		$method = '',
		$closesession = '',
		$ignoresef = '',
		$cbreplace = 0,
		$replprint = 1,
		$repltmpl = 1)
	{
		// Get document and doctype
		$this->_getdoc();

		// Define the regular expression for the bot
		$this->regex = "#(<p\b[^>]*>\s*)?{component\surl='(.*?)'\s*}(\s*</p>)?#s";

		// Get ignores for stylesheets and scripts
		$this->ignore_scripts = $ignore_script;
		$this->ignore_scripts = preg_split("/[\n\r]+/", $this->ignore_scripts);
		$this->ignore_styles = $ignore_style;
		$this->ignore_styles = preg_split("/[\n\r]+/", $this->ignore_styles);

		// Get method
		$this->method = $method;
		$this->closesession = $closesession;
		$this->ignoresef = $ignoresef;
		$this->cbreplace = $cbreplace;
		$this->replprint = $replprint;
		$this->repltmpl = $repltmpl;

		// What is the url of website without / at the end
		$this->url = preg_replace('/\/$/', '', JURI::base());
		$this->base = JURI::base(true);
	}

	/**
	 * _getdoc
	 *
	 * @return  the output
	 *
	 * @since   1.0
	 */
	public function _getdoc()
	{
		if ($this->document == null)
		{
			$this->document = JFactory::getDocument();
			$this->doctype = $this->document->getType();
		}
	}

	/**
	 * process
	 *
	 * @param   mixed  $url  the params
	 * 
	 * @return  the output
	 *
	 * @since   1.0
	 */
	public function process($url)
	{
		// Clean url
		$reg[] = "/<span[^>]*?>/si";
		$repl[] = '';
		$reg[] = "/<\/span>/si";
		$repl[] = '';
		$url = preg_replace($reg, $repl, trim($url));
		$origurl = JUri::base(true) . $url;
		$origurl = preg_replace('/&amp;/', '&', $origurl);

		if (strpos($url, 'index.php') !== false || $this->ignoresef == "1")
		{
			$sef = false;
			$url = $url . ((strpos($url, '?') === false) ? '?' : '&') . 'tmpl=component&print=1';

			// Add origin too to the component so it can redirect to the origin if something goes wrong
			$url .= '&origin=' . base64_encode(JUri::getInstance()->toString());
		}
		else
		{
			$sef = true;
			$url = $url . ((substr($url, -1) != '/') ? '/' : '') . 'tmpl,component/print,1';

			// Add origin too to the component so it can redirect to the origin if something goes wrong
			$url .= '/origin=' . base64_encode(JUri::getInstance()->toString());
		}

		$url = JUri::base() . $url;

		// We need to replace the &amp; to & because the &amp; is not recognized
		$url = preg_replace('/&amp;/', '&', $url);

		$ok = false;
		$postcurl = array();
		$post = '';
		$cookie = '';
		$reg = '/^[a-f0-9]+$/si';

		// Get all session parameters
		foreach ($_COOKIE as $key => $value)
		{
			if (preg_match($reg, $key) > 0)
			{

				// Separation in cookies is ; with space!
				$cookie .= "$key=$value; ";
				$postcurl[$key] = $value;
				if ($sef)
				{
					$post.=((strlen($post) > 0) ? '/' : '') . "$key,$value";
				}
				else
				{
					$post.=((strlen($post) > 0) ? '&' : '') . "$key=$value";
				}
			}
		}

		// Close session so the other component can use it
		if ($this->closesession == "1")
		{
			$session = & JFactory::getSession();
			$session->close();
		}

		if (ini_get('allow_url_fopen') && $this->method != 'curl')
		{
			if ($response = @file_get_contents($url . ((strlen($post) > 0) ? $post : '')))
			{
				$ok = true;
			}
		}

		if (!$ok)
		{
			if (function_exists('curl_init'))
			{
				$ch = curl_init($url);

				// Set curl options, see: http://www.php.net/manual/en/function.curl-setopt.php

				// To return the transfer as a string
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

				// The contents of the "User-Agent: " header
				curl_setopt($ch, CURLOPT_USERAGENT, 'spider');

				// Set referer on redirect
				curl_setopt($ch, CURLOPT_AUTOREFERER, true);

				// Send authentication
				$username = "";
				$password = "";

				// Mod_php
				if (isset($_SERVER['PHP_AUTH_USER']))
				{
					$username = $_SERVER['PHP_AUTH_USER'];
					$password = $_SERVER['PHP_AUTH_PW'];
				}

				// Most other servers
				elseif (isset($_SERVER['HTTP_AUTHENTICATION']))
				{
					if (strpos(strtolower($_SERVER['HTTP_AUTHENTICATION']), 'basic') === 0)
					{
						list($username, $password) = explode(':', base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'], 6)));
					}
				}
				if ($username != "" && $password != "")
				{
					curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);

					// Set referer on redirect
					curl_setopt($ch, CURLOPT_USERPWD, $username . ":" . $password);
				}

				// Set to zero for no timeout
				$timeout = 5;
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);

				// Stop after 10 redirects
				curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

				if (strlen($cookie) > 0)
				{

					// False to keep all cookies of previous session
					curl_setopt($ch, CURLOPT_COOKIESESSION, false);
					curl_setopt($ch, CURLOPT_COOKIE, $cookie);
				}
				if (strlen($post) > 0)
				{
					curl_setopt($ch, CURLOPT_POST, true);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $postcurl);
				}

				$response = curl_exec($ch);
				if (curl_errno($ch))
				{
					$response = "<!-- url not received: #" . curl_errno($ch) . " \"" . curl_error($ch) . "\" -->";
				}
				else
				{
					$ok = true;
				}
				curl_close($ch);
			}
			else
			{
				$response = "<!-- curl not available as PHP library -->";
			}
		}

		// Start the session again?
		if ($ok)
		{
			return $response;
		}
		return null;
	}

	/**
	 * _addScript
	 *
	 * @param   mixed  $script  the params
	 * 
	 * @return  the output
	 *
	 * @since   1.0
	 */
	public function _addScript($script)
	{
		$found = false;

		foreach ($this->ignore_scripts as $url)
		{
			if ($url == $script || ($this->url . $url == $script) || ($this->url . "/" . $url == $script) || ($this->base . "/" . $url == $script))
			{
				$found = true;
			}
		}
		if (!$found)
		{
			$this->document->addScript($script);
		}
	}

	/**
	 * _addStyleSheet
	 *
	 * @param   mixed  $style  the params
	 * 
	 * @return  the output
	 *
	 * @since   1.0
	 */
	public function _addStyleSheet($style)
	{
		$found = false;

		foreach ($this->ignore_styles as $url)
		{
			if ($url == $style || ($this->url . $url == $style) || ($this->url . "/" . $url == $style) || ($this->base . "/" . $url == $style))
			{
				$found = true;
			}
		}
		if (!$found)
		{
			$this->document->addStyleSheet($style);
		}
	}

}
