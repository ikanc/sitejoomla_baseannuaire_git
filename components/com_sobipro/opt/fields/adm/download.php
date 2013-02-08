<?php
/**
 * @version: $Id: download.php 1005 2011-03-24 12:32:08Z Radek Suski $
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
 * $Date: 2011-03-24 13:32:08 +0100 (Thu, 24 Mar 2011) $
 * $Revision: 1005 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Addons/trunk/Apps/DownloadField/FieldAdm/download.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.download' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 28-Nov-2009 20:06:23
 */
class SPField_AdmDownload extends SPField_Download implements SPFieldInterface
{
	public function onFieldEdit( &$view )
	{
		SPLang::load( 'SpApp.download' );
		$get = SPFactory::Controller( 'acl', true )->userGroups();
		$groups = array();
		foreach ( $get as $group ) {
			$groups[ $group[ 'value' ] ] = $group[ 'text' ];
		}

		$licenses = array();
		$get = SPFactory::db()->select(
			array( 'sValue', 'language', 'explanation', 'id' ), 'spdb_language',
			array( 'sKey' => 'license', 'oType' => 'app_download', 'language' => array( Sobi::Lang(), SOBI_DEFLANG, 'en-GB' ) )
		)->loadAssocList( 'id' );

		foreach ( $get as $license ) {
			$licenses[ $license[ 'id' ] ] = $license[ 'sValue' ];
		}
		$this->allowedExt = implode( ', ', $this->allowedExt );
		$view->assign( $groups, 'downloadGroups' );
		$view->assign( $groups, 'uploadGroups' );
		$view->assign( $licenses, 'licenses' );
	}

	public function save( &$attr )
	{
		$attr[ 'maxSize' ] = $attr[ 'maxSize' ] * SPRequest::int( 'sizeMulti', 1, 'post' );
		$attr[ 'allowedExt' ] = explode( ',', $attr[ 'allowedExt' ] );
		SPLang::load( 'SpApp.download' );
		$maxSize = ( int ) ini_get( 'upload_max_filesize' );
		$maxSizeUnit = substr( ini_get( 'upload_max_filesize' ), -1 );
		if( $maxSize && $maxSize < 0 ) {
			$multi = array( 'b' => 1, 'k' => 1024, 'm' => 1048576, 'g' => 1073741824 );
			$maxSize = $maxSize * $multi[ strtolower( $maxSizeUnit ) ];
			if( $maxSize < $attr[ 'maxSize' ] ) {
				SPMainFrame::msg( Sobi::Txt( 'DWNA.FM.LIMIT_HIGHER_THAN_PHP' ), SPC::ERROR_MSG );
				$attr[ 'maxSize' ] = $maxSize - 1;
			}
		}
		if( count( $attr[ 'allowedExt' ] ) ) {
			foreach ( $attr[ 'allowedExt' ] as $i => $ext ) {
				$attr[ 'allowedExt' ][ $i ] = trim( $ext );
			}
		}
		$myAttr = $this->getAttr();
		$properties = array();
		if( count( $myAttr ) ) {
			foreach ( $myAttr as $property ) {
				$properties[ $property ] = isset( $attr[ $property ] ) ? ( $attr[ $property ] ) : null;
			}
		}
		$attr[ 'params' ] = $properties;
	}
}