<?php
/**
 * @version: $Id: download.php 971 2011-03-10 13:59:51Z Radek Suski $
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
 * $Date: 2011-03-10 14:59:51 +0100 (Thu, 10 Mar 2011) $
 * $Revision: 971 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Addons/trunk/Apps/DownloadField/Ctrl/download.php $
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
	protected $_defTask = 'file';
	/**
	 */
	public function execute()
	{
		SPLang::load( 'SpApp.download' );
		$this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
		switch ( $this->_task ) {
			case 'file':
				$this->file();
				break;
		}
	}

	private function file()
	{
		$fid = SPRequest::cmd( 'fid' );
		$fid = explode( '.', $fid );
		$sid = ( int ) $fid[ 1 ];
		$fid = ( int ) $fid[ 0 ];
		$file = SPFactory::db()->select( '*', 'spdb_field_data', array( 'fid' => $fid, 'sid' => $sid, 'enabled' => 1, 'copy' => 0 ) )->loadObject();
		if( !( $file ) ) {
			Sobi::Error( 'Download Field', SPLang::e( 'DWLA_FE_ERR_NO_FILE', SPRequest::cmd( 'fid' ) ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		$fileData = $this->fileData( $file->baseData );
		if( $fileData[ 'license' ] ) {
			$lid = SPRequest::int( 'license_confirm', 0 );
			if( $lid != $fid ) {
				Sobi::Error( 'Download Field', SPLang::e( 'DWLA_FE_ERR_CONFIRM_LIC' ), SPC::WARNING, 403, __LINE__, __FILE__ );
			}
		}
		if( !( $this->checkPermission( $fid, $fileData ) ) ) {
			Sobi::Error( 'Download Field', SPLang::e( 'DWLA_FE_ERR_NO_PERMS' ), SPC::WARNING, 403, __LINE__, __FILE__ );
		}
		$data = SPFs::read( $fileData[ 'abs_path' ] );
		if( !( strlen( $data ) ) ) {
			Sobi::Error( 'Download Field', SPLang::e( 'DWLA_FE_ERR_NO_FILE', basename( $fileData[ 'path' ] ) ), SPC::WARNING, 500, __LINE__, __FILE__ );
		}
		$fname = basename( $fileData[ 'abs_path' ] );
		SPFactory::db()->insert( 
			'spdb_downloads', 
				array ( 
					'FUNCTION:NOW()', $fname, Sobi::My( 'id' ), 
					$sid, $fid, SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' ), 
					filesize( $fileData[ 'abs_path' ] ), $fileData[ 'checksum' ][ 'md5' ]
				) 
		);
		SPFactory::mainframe()->cleanBuffer();
		header( "Content-type: {$fileData[ 'mime_type' ]}" );
		header( "Content-Disposition: attachment; filename=\"{$fname}\"" );
		header( 'Content-Length: ' . filesize( $fileData[ 'abs_path' ] ) );
		ob_clean();
		flush();
		echo $data;
		exit;
	}

	private function checkPermission( $fid, $file )
	{
		$field = SPFactory::Model( 'field' );
		$field->init( $fid );
		$allowed = false;
		$gids = array();
		$info = array();
		$userGids = Sobi::My( 'gid' );
		if( !( SPFactory::user()->isAdmin() ) ) {
			if( $field->get( 'authorAclPerms' ) ) {
				if( count( $file[ 'downloaders' ] ) ) {
					foreach ( $file[ 'downloaders' ] as $gid ) {
						if( in_array( $gid, $field->get( 'downloadGroups' ) ) ) {
							$gids[] = $gid;
						}
					}
				}
			}
			else {
				$gids = $field->get( 'downloadGroups' );
			}
			if( count( $userGids ) ) {
				foreach ( $userGids as $gid ) {
					if( in_array( $gid, $gids ) ) {
						$allowed = true;
						break;
					}
				}
			}
		}
		else {
			$allowed = true;
		}
		return $allowed;
	}

	private function fileData( $f )
	{
		if( strlen( $f ) ) {
			$f = unserialize( $f );
			if( is_array( $f ) ) {
				if( SPFs::exists( $f[ 'abs_path' ] ) ) {
					$fPath = $f[ 'abs_path' ];
				}
				elseif( SPFs::exists( SOBI_ROOT.$f[ 'path' ] ) ) {
					$fPath = SOBI_ROOT.$f[ 'path' ];
					$f[ 'abs_path' ] = SOBI_ROOT.$f[ 'path' ];
				}
				if( $fPath ) {
					if( !( md5_file( $fPath ) == $f[ 'checksum' ][ 'md5' ] ) ) {
						Sobi::Error( 'Download Field', SPLang::e( 'DWLA_FE_ERR_MD5_CHECKSUM', basename( $f[ 'path' ] ) ), SPC::WARNING, 500, __LINE__, __FILE__ );
						return false;
					}
					if( !( sha1_file( $fPath ) == $f[ 'checksum' ][ 'sha1' ] ) ) {
						Sobi::Error( 'Download Field', SPLang::e( 'DWLA_FE_ERR_SHA1_CHECKSUM', basename( $f[ 'path' ] ) ), SPC::WARNING, 500, __LINE__, __FILE__ );
						return false;
					}
				}
				else {
					Sobi::Error( 'Download Field', SPLang::e( 'DWLA_FE_ERR_NO_FILE', basename( $f[ 'path' ] ) ), SPC::WARNING, 500, __LINE__, __FILE__ );
				}
			}
		}
		return $f;
	}
}
?>