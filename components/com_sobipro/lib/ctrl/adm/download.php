<?php
/**
 * @version: $Id: download.php 775 2011-02-10 22:27:39Z Radek Suski $
 * @package: SobiPro Library
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/lgpl.html GNU/LGPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU Lesser General Public License version 3
 * ===================================================
 * $Date: 2011-02-10 23:27:39 +0100 (Thu, 10 Feb 2011) $
 * $Revision: 775 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Addons/trunk/Apps/DownloadField/Ctrl/adm/download.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'controller' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 15-Jul-2010 18:17:28
 */
class SPDownload extends SPController
{
	/**
	 * @var string
	 */
	protected $_defTask = 'licenses';
	public function __construct() {}
	/**
	 */
	public function execute()
	{
		SPLang::load( 'SpApp.download' );
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'licenses':
				$this->licenses();
				break;
			case 'license':
				$this->license();
				break;
			case 'saveLicense':
				$this->saveLicense();
				break;
			case 'ldelete':
				$this->delLicense();
				break;
		}

	}

	protected function delLicense()
	{
		$lic = SPRequest::int( 'lid', 0, 'get' );
		SPFactory::db()->delete( 'spdb_language', array( 'sKey' => 'license', 'oType' => 'app_download', 'id' => $lic ) );
		Sobi::Redirect( Sobi::Url( array( 'task' => 'download.licenses', 'out' => 'html' ) ) );
	}

	protected function saveLicense()
	{
		$title = SPRequest::string( 'license_title', null, false, 'post' );
		$txt = SPRequest::string( 'license_text', null, 2, 'post' );
		$lic = SPRequest::int( 'lic', 0, 'post' );
		if( !( $lic ) ) {
			$lic = SPFactory::db()->select( 'MAX( id )', 'spdb_language', array( 'sKey' => 'license', 'oType' => 'app_download' ) )->loadResult();
			$lic++;
		}
		SPFactory::db()->replace( 'spdb_language', array( 'license', $title, 0, Sobi::Lang(), 'app_download', 0, $lic, '', '', $txt ) );
		Sobi::Redirect( Sobi::Url( array( 'task' => 'download.licenses', 'out' => 'html', 'a' => 1 ) ) );
	}


	protected function licenses()
	{
		$lic = SPFactory::db()->select(
			array( 'sValue', 'language', 'explanation', 'id' ), 'spdb_language',
			array( 'sKey' => 'license', 'oType' => 'app_download', 'language' => array( Sobi::Lang(), SOBI_DEFLANG, 'en-GB' ) )
		)->loadAssocList( 'id' );
		$licenses = array();
		foreach ( $lic as $i => $l ) {
			$licenses[] = array(
				'id' => $l[ 'id' ],
				'title' => $l[ 'sValue' ],
				'text' => $l[ 'explanation' ],
				'url' => Sobi::Url( array( 'task' => 'download.license', 'lid' => $l[ 'id' ], 'out' => 'html' ) ),
				'durl' => Sobi::Url( array( 'task' => 'download.ldelete', 'lid' => $l[ 'id' ], 'out' => 'html' ) )
			);
		}
		$aurl = Sobi::Url( array( 'task' => 'download.license', 'out' => 'html' ) );
		$view =& SPFactory::View( 'view', true );
		$view->assign( $this->_task, 'task' );
		$view->assign( $licenses, 'licenses' );
		$view->assign( $aurl, 'addUrl' );
		$view->addHidden( $lic, 'lic' );
		$view->setTemplate( 'field.licenses' );
		$view->display();
	}

	protected function license()
	{
		$lid = SPRequest::int( 'lid', 0 );
		if( $lid ) {
			$lic = SPFactory::db()->select(
				array( 'sValue', 'language', 'explanation', 'id' ), 'spdb_language',
				array( 'sKey' => 'license', 'oType' => 'app_download', 'id' => $lid, 'language' => array( Sobi::Lang(), SOBI_DEFLANG, 'en-GB' ) )
			)->loadObject();
			$license = array( 'id' => $lic->id, 'title' => $lic->sValue, 'text' => $lic->explanation );
		}
		else {
			$license = array( 'id' => '', 'title' => '', 'text' => '' );
		}
		$task = 'download.saveLicense';
		$burl = Sobi::Url( array( 'task' => 'download.licenses', 'out' => 'html' ) );
		$view =& SPFactory::View( 'view', true );
		$raw = Sobi::Url( array( 'out' => 'raw' ), true );
		$raw = explode( '&', $raw );
		$raw = explode( '=', $raw[ 1 ] );
		$view->addHidden( $raw[ 1 ], $raw[ 0 ] );
		$view->loadConfig( 'field.edit' );
		$view->assign( $license, 'license' );
		$view->assign( $burl, 'backUrl' );
		$view->assign( $task, 'task' );
		$view->addHidden( $task, 'task' );
		$view->addHidden( $lid, 'lic' );
		$view->setTemplate( 'field.license' );
		$view->display();
	}
}
?>