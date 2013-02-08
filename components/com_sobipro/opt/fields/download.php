<?php
/**
 * @version: $Id: download.php 949 2011-03-07 18:48:47Z Radek Suski $
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
 * $Date: 2011-03-07 19:48:47 +0100 (Mon, 07 Mar 2011) $
 * $Revision: 949 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Addons/trunk/Apps/DownloadField/download.php $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.inbox' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 28-Nov-2009 20:06:23
 */
class SPField_Download extends SPField_Inbox implements SPFieldInterface
{
	/**
	 * @var double
	 */
	protected $maxSize =  1048576;
	/**
	 * @var string
	 */
	protected $savePath = 'media/upload/{id}/';
	/**
	 * @var array
	 */
	protected $uploadGroups = array();
	/**
	 * @var array
	 */
	protected $downloadGroups = array();
	/**
	 * @var bool
	 */
	protected $allowLicenses = false;
	/**
	 * @var array
	 */
	protected $licenses = array();
	/**
	 * @var bool
	 */
	protected $authorAclPerms = false;
	/**
	 * @var bool
	 */
	protected $stats = false;
	/**
	 * @var array
	 */
	protected $allowedExt = array();
	/**
	 * @var array
	 */
	protected $displayAs = array( 'image', 'text' );
	/**
	 * @var array
	 */
	private static $mimeIcons = array();
	/**
	 * @var string
	 */
	protected $cssClass = '';
	/**
	 * @var bool
	 */
	protected $editIcon = true;
	
	/**
	 * Returns the parameter list
	 * @return array
	 */
	protected function getAttr()
	{
		return array( 	
			'savePath', 'maxSize', 'uploadGroups',
			'downloadGroups', 'allowLicenses', 'licenses',
			'authorAclPerms', 'allowedExt', 'maxSize',
			'displayAs', 'editIcon'
		);
	}

	/**
	 * Shows the field in the edit entry or add entry form
	 * @param bool $return return or display directly
	 * @return string
	 */
	public function field( $return = false )
	{
		SPLang::load( 'SpApp.download' );
		if( !( $this->enabled ) ) {
			return false;
		}
		$field = null;
		$file = $this->fileData();
		$gids = array();
		if( ( $this->checkPerms( $file, 'uploaders', $gids ) ) ) {
			$class =  $this->required ? $this->cssClass.' required' : $this->cssClass;
			$show = null;
			$field = null;
			$params = array( 'id' => $this->nid, 'class' => $class );
			if( $this->width ) {
				$params[ 'style' ] = "width: {$this->width}px;";
			}		
			if( $file ) {
				$size = round( $file[ 'size' ] / 1024, 2 );
				$field .= "\n<div id=\"{$this->nid}_file_preview\" >";
				if( $this->editIcon ) {
					$field .= "\n\t<div style=\"float: left;\"><img src=\"{$file[ 'icon' ]}\" alt=\"{$this->name}\"/></div>";
				}
				$field .= '<div style="float: left; margin-left: 5px;">';
				$field .= '<span><b>'.Sobi::Txt( 'DWLA_FE_FLD_EDIT_FILENAME' ).'</b>'.basename( $file[ 'abs_path' ] ).'</span><br/>';
				$field .= '<span><b>'.Sobi::Txt( 'DWLA_FE_FLD_EDIT_FILESIZE' ).'</b>'.$size.' kB</span><br/>';
				$field .= SPHtml_Input::checkbox( $this->nid.'_delete', 1, Sobi::Txt( 'DWLA_FE_FLD_EDIT_DELETE' ), $this->nid.'_delete', false, array( 'class' => $this->cssClass ) );
				$field .= "\n</div></div>\n";
				$field .= "\n<div style=\"clear:both\"></div>\n";
			}
			$field .= SPHtml_Input::file( $this->nid, 20, $params );
			if( $this->authorAclPerms ) {
				SPFactory::header()->addCssCode( ' #'.$this->nid.'_downloaders_cont span { display: block; float: left; min-width: 100px; }' );
				$gids = SPUser::groups( $this->downloadGroups );
				$gids[ 0 ] = Sobi::Txt( 'DWLA_FE_FLD_EDIT_ACL_VISITOR' );
				$field .= '<div style="clear:both; margin-left: 5px;" id="'.$this->nid.'_downloaders_cont">';
				$field .= '<br/><b>'.Sobi::Txt( 'DWLA_FE_FLD_EDIT_ACL' ).'</b><br/>';
				$field .= SPHtml_Input::checkBoxGroup( $this->nid.'_downloaders', $gids, $this->nid.'_downloaders', $file[ 'downloaders' ] );
				$field .= "\n</div>\n<div style=\"clear:both\"></div><br/>\n";
			}
			if( $this->allowLicenses && count( $this->licenses ) ) {
				$licFull = $this->getLicenses();
				$field .= '<div style="clear:both; margin-left: 5px;" id="'.$this->nid.'_license_cont">';
				$field .= '<br/><b>'.Sobi::Txt( 'DWLA_FE_FLD_EDIT_LICENSE' ).'</b>';
				$field .= SPHtml_Input::select( $this->nid.'_license', $licFull, $file[ 'license' ] );
				$field .= "\n</div>\n<div style=\"clear:both\"></div><br/>\n";
	
			}
		}
		else {
			$field .= '<br/><b>'.Sobi::Txt( 'DWLA_NO_UPLOAD_PERMS' ).'</b>';
		}
		if( !$return ) {
			echo $field;
		}
		else {
			return $field;
		}
	}

	private function checkPerms( $file, $group, &$gids )
	{		
		/* handle permissions */
		$allowed = false;
		$data = array();
		$userGids = Sobi::My( 'gid' );
		$iGrp = $group == 'downloaders' ? $this->downloadGroups : $this->uploadGroups;
		if( $this->authorAclPerms && ( $group == 'downloaders' ) ) {
			if( count( $file[ $group ] ) ) {
				foreach ( $file[ $group ] as $gid ) {
					if( in_array( $gid, $iGrp ) ) {
						$gids[] = $gid;
					}
				}
			}
		}
		else {
			$gids = $iGrp;
		}
		if( !( SPFactory::user()->isAdmin() ) ) {
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
	
	private function getLicenses( $full = false, $id = 0 )
	{
		$s = array( 'sValue', 'id' );
		if( $full ) {
			$s[] = 'explanation';
		}
		$id = $id ? $id : $this->licenses;
		$licenses = SPFactory::db()->select(
			$s, 'spdb_language',
			array( 'sKey' => 'license', 'oType' => 'app_download', 'language' => array( Sobi::Lang(), SOBI_DEFLANG ), 'id' => $id )
		)->loadAssocList( 'id' );
		
		if( count( $licenses ) ) {
			foreach ( $licenses as $lid => $license ) {
				if( !( $full ) ) {
					$licenses[ $lid ] = $license[ 'sValue' ];
				}
				else {
					$licenses[ $lid ] = array( 'name' => $license[ 'sValue' ], 'text' => $license[ 'explanation' ] );
				}
			}
		}
		return $licenses;
	}

	private function fileData()
	{
		$file = false;
		$f = $this->getRaw();
		if( strlen( $f ) ) {
			$f = unserialize( $f );
			if( is_array( $f ) ) {
				if( !( count( self::$mimeIcons ) ) ) {
					self::$mimeIcons = SPLoader::loadIniFile( 'etc.mime_icons' );
				}
				if( isset(  $f[ 'abs_path' ] ) ) {
					if( SPFs::exists( $f[ 'abs_path' ] ) ) {
						$fPath = $f[ 'abs_path' ];
					}
					elseif( SPFs::exists( SOBI_ROOT.$f[ 'path' ] ) ) {
						$fPath = SOBI_ROOT.$f[ 'path' ];
						$f[ 'abs_path' ] = SOBI_ROOT.$f[ 'path' ];
					}
				}
				if( isset( $fPath ) && $fPath ) {
					if( !( md5_file( $fPath ) == $f[ 'checksum' ][ 'md5' ] ) ) {
						Sobi::Error( 'Download Field', SPLang::e( 'DWLA_FE_ERR_MD5_CHECKSUM', $f[ 'path' ] ), SPC::WARNING, 0, __LINE__, __FILE__ );
						return false;
					}
					if( !( sha1_file( $fPath ) == $f[ 'checksum' ][ 'sha1' ] ) ) {
						Sobi::Error( 'Download Field', SPLang::e( 'DWLA_FE_ERR_SHA1_CHECKSUM', $f[ 'path' ] ), SPC::WARNING, 0, __LINE__, __FILE__ );
						return false;
					}
					$mimeType = explode( '/', $f[ 'mime_type' ] );
					if( isset( self::$mimeIcons[ $mimeType[ 0 ] ] ) ) {
						if( isset( self::$mimeIcons[ $mimeType[ 0 ] ][ $mimeType[ 1 ] ] ) ) {
							$f[ 'icon' ] = self::$mimeIcons[ $mimeType[ 0 ] ][ $mimeType[ 1 ] ];
						}
						elseif( isset( self::$mimeIcons[ $mimeType[ 0 ] ][ 'generic' ] ) ) {
							$f[ 'icon' ] = self::$mimeIcons[ $mimeType[ 0 ] ][ 'generic' ];
						}
						else {
							$f[ 'icon' ] = self::$mimeIcons[ 'generic' ];
						}
					}
					else {
						$f[ 'icon' ] = self::$mimeIcons[ 'generic' ];
					}
					$f[ 'icon' ] = Sobi::Cfg( 'img_folder_live' ).'/icons/'.$f[ 'icon' ];
					if( !( isset( $f[ 'downloaders' ] ) ) ) {
						$f[ 'downloaders' ] = array();
					}
					if( !( isset( $f[ 'license' ] ) ) ) {
						$f[ 'license' ] = null;
					}
					if( !( isset( $f[ 'upload_date' ] ) ) ) {
						$f[ 'upload_date' ] = null;
					}
				}
				else {
					return null;
				}
			}
		}
		return $f;
	}

	private function parseName( $entry, $name, $pattern )
	{
		$placeHolders = array( '/{id}/', '/{orgname}/', '/{entryname}/' );
		$replacements = array( $entry->get( 'id' ), $name, $entry->get( 'nid' ) );
		return preg_replace( $placeHolders, $replacements, $pattern );
	}

	private function fromCache( $cache )
	{
		$tsid = SPRequest::string( 'editentry', null, false, 'cookie' );
		/* @TODO muss mir hier was ausdenken */
	}

	private function checkFile( $file )
	{
		$ext = strtolower( SPFs::getExt( $file ) );
		if( !( in_array( $ext, $this->allowedExt ) ) ) {
			SPFs::delete( $file );
			throw new SPException( SPLang::e( 'DWLA_FE_ERR_UPL_WRONG_TYPE', $ext ) );
		}
		$allowed = SPLoader::loadIniFile( 'etc.download' );
		$mType = SPFactory::Instance( 'services.fileinfo', $file )->mimeType();
		if( strlen( $mType ) && !( in_array( $mType, $allowed ) ) ) {
			SPFs::delete( $file );
			throw new SPException( SPLang::e( 'DWLA_FE_ERR_UPL_WRONG_TYPE', $ext.' - '.$mType ) );
		}
		return $mType;
	}

	/**
	 * Gets the data for a field, verify it and pre-save it.
	 * @param SPEntry $entry
	 * @param string $request
	 * @return void
	 */
	public function submit( &$entry, $tsid = null, $request = 'POST' )
	{
		SPLang::load( 'SpApp.download' );
		$save = array();
		if( $this->verify( $entry, $request ) ) {
			if( SPRequest::file( $this->nid, 'size' ) > $this->maxSize ) {
				throw new SPException( SPLang::e( 'DWLA_FE_ERR_UPL_TOO_LARGE', $this->name, $this->maxSize/1024, $fileSize/1024 ) );
			}
			/* save the file to temporary folder */
			$data = SPRequest::file( $this->nid, 'tmp_name' );
			if( $data ) {
				$path = SPLoader::dirPath( "tmp.edit.{$tsid}.files", 'front', false );
				$path .= DS.SPRequest::file( $this->nid, 'name' );
				$fileClass = SPLoader::loadClass( 'base.fs.file' );
				$file = new $fileClass();
				$file->upload( $data, $path );
				$save[ $this->nid ] = $path;
				$this->checkFile( $file->getPathname() );
			}
			$save[ $this->nid.'_delete' ] = SPRequest::bool( $this->nid.'_delete' );
		}
		return $save;
	}

	/**
	 * @param SPEntry $entry
	 * @param string $request
	 * @return bool
	 */
	private function verify( $entry, $request )
	{
		if( strtolower( $request ) == 'post' || strtolower( $request ) == 'get' ) {
			$data = SPRequest::file( $this->nid, 'tmp_name' );
		}
		else {
			$data = SPRequest::file( $this->nid, 'tmp_name', $request );
		}
		$del = SPRequest::bool( $this->nid.'_delete', false, $request );
		$dexs = strlen( $data );

		/* check if there was an adminField */
		if( $this->adminField && ( $dexs || $del ) ) {
			if( !( Sobi:: Can( 'entry.adm_fields.edit' ) ) ) {
				throw new SPException( SPLang::e( 'FIELD_NOT_AUTH', $this->name ) );
			}
		}

		/* check if it was free */
		if( !( $this->isFree ) && $this->fee && $dexs ) {
			SPFactory::payment()->add( $this->fee, $this->name, $entry->get( 'id' ), $this->fid );
		}

		/* check if it was editLimit */
		if( $this->editLimit == 0 && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_EXP', $this->name ) );
		}

		/* check if it was editable */
		if( !( $this->editable ) && !( Sobi::Can( 'entry.adm_fields.edit' ) ) && $dexs && $entry->get( 'version' ) > 1 ) {
			throw new SPException( SPLang::e( 'FIELD_NOT_AUTH_NOT_ED', $this->name ) );
		}
		return true;
	}

	/**
	 * Gets the data for a field and save it in the database
	 * @param SPEntry $entry
	 * @return bool
	 */
	public function saveData( &$entry, $request = 'POST' )
	{
		SPLang::load( 'SpApp.download' );
		if( !( $this->enabled ) ) {
			return false;
		}
		$del = SPRequest::bool( $this->nid.'_delete', false, $request );
		static $store = null;
		$mime = null;
		$cache = false;
		if( $store == null ) {
			$store = SPFactory::registry()->get( 'requestcache_stored' );
		}
		if( is_array( $store ) && isset( $store[ $this->nid ] ) ) {
			$data = $store[ $this->nid ];
			$cache = true;
			$orgName = SPRequest::file( $this->nid, 'name', $request );
		}
		else {
			$data = SPRequest::file( $this->nid, 'tmp_name' );
			$orgName = SPRequest::file( $this->nid, 'name' );
		}
		$current = $this->fileData();
		$dexs = strlen( $data );
		$files = array();
		$sPath = $this->parseName( $entry, $orgName, $this->savePath );
		$orgFile = null;
		$path = Sobi::FixPath( SPLoader::dirPath( $sPath, 'root', false ) );
		/* create directory if not exists */
		if( !( SPFs::exists( $path ) ) ) {
			if( !( SPFs::mkdir( $path ) ) ) {
				throw new SPException( SPLang::e( 'DWLA_FE_ERR_CANNOT_CREATE_PATH' ) );
			}
		}
		/* if we have an image */
		if( $data ) {
			$fileSize = SPRequest::file( $this->nid, 'size' );
			if( $fileSize > $this->maxSize ) {
				throw new SPException( SPLang::e( 'DWLA_FE_ERR_UPL_TOO_LARGE', $this->name, $this->maxSize/1024, $fileSize/1024 ) );
			}
			$fileName = $this->parseName( $entry, $orgName, $this->savePath.DS.'{orgname}' );
			if( $cache ) {
				$orgFile = SPFactory::Instance( 'base.fs.file', $data );
				$orgFile->move( $path.$orgName );
			}
			else {
				$orgFile = SPFactory::Instance( 'base.fs.file' );
				$orgFile->upload( $data, $path.$orgName );
			}
			$mime = $this->checkFile( $orgFile->getPathname() );
			if( Sobi::Cfg( 'download.no_direct_access', true ) && !( SPFs::exists( $path.DS.'.htaccess' ) ) ) {
				$ht = "<Files *>\n\tOrder Deny,Allow\n\tDeny from all\n\tAllow from localhost\n</Files>";
				SPFs::write( Sobi::FixPath( $path.DS.'.htaccess' ), $ht );
			}
		}
		/* otherwise deleting a file */
		elseif( $del ) {
			$this->delFile( $this->parseName( $entry, null, $this->savePath ), $entry->get( 'id' ) );
		}
		/* something changed in the license ? */
		elseif( SPRequest::arr( $this->nid.'_license', null, $request ) ) {
			//continue;
		}
		/* something changed users list ? */
		elseif( count( SPRequest::arr( $this->nid.'_downloaders', array(), $request ) ) ) {
			//continue;
		}
		/* otherwise just break */
		elseif( !( count( $current ) ) ) {
			return true;
		}

		/* @var SPdb $db */
		$db =& SPFactory::db();
		$this->verify( $entry, $request );

		$time = SPRequest::now();
		$IP = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
		$uid = Sobi::My( 'id' );

		/* if we are here, we can save these data */
		/* collect the needed params */
		$save = array();
		if( $orgFile ) {
			$f = $orgFile->getPathname();
			$save[ 'path' ] = str_replace( SOBI_ROOT, null, $f );
			$save[ 'abs_path' ] = $f;
			$save[ 'checksum' ] = array( 'md5' => md5_file( $f ), 'sha1' => sha1_file( $f ) );
			$save[ 'mime_type' ] = $mime;
			$save[ 'size' ] = filesize( $f );
			$save[ 'downloaders' ] = SPRequest::arr( $this->nid.'_downloaders', array(), $request );
			$save[ 'license' ] = SPRequest::int( $this->nid.'_license', 0, $request );
			$save[ 'upload_date' ] = SPFactory::config()->date( time() );
		}
		else {
			$save = $current;
			$save[ 'downloaders' ] = SPRequest::arr( $this->nid.'_downloaders', array(), $request );
			$save[ 'license' ] = SPRequest::int( $this->nid.'_license', 0, $request );
		}
		$save = serialize( $save );
		$params = array();
		$params[ 'publishUp' ] = $entry->get( 'publishUp' );
		$params[ 'publishDown' ] = $entry->get( 'publishDown' );
		$params[ 'fid' ] = $this->fid;
		$params[ 'sid' ] = $entry->get( 'id' );
		$params[ 'section' ] = Sobi::Reg( 'current_section' );
		$params[ 'lang' ] = Sobi::Lang();
		$params[ 'enabled' ] = $entry->get( 'state' );
		$params[ 'baseData' ] = $db->escape( $save );
		$params[ 'approved' ] = $entry->get( 'approved' );
		$params[ 'confirmed' ] = $entry->get( 'confirmed' );
		/* if it is the first version, it is new entry */
		if( $entry->get( 'version' ) == 1 ) {
			$params[ 'createdTime' ] = $time;
			$params[ 'createdBy' ] = $uid;
			$params[ 'createdIP' ] = $IP;
		}
		$params[ 'updatedTime' ] = $time;
		$params[ 'updatedBy' ] = $uid;
		$params[ 'updatedIP' ] = $IP;
		$params[ 'copy' ] = !( $entry->get( 'approved' ) );

		/* save it */
		try {
			$db->insertUpdate( 'spdb_field_data', $params );
		}
		catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_SAVE_FIELDS_DATA_DB_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}
	/* (non-PHPdoc)
	 * @see Site/opt/fields/SPFieldType#deleteData($sid)
	 */
	public function deleteData( $sid )
	{
		$this->delFile();
	}

	private function delFile( $file, $sid )
	{
		$file = $this->fileData();
		if( $file && SPFs::exists( $file[ 'abs_path' ] ) ) {
			SPFs::delete( $file[ 'abs_path' ]  );
		}
		try {
			SPFactory::db()->delete( 'spdb_field_data', array( 'sid' => $sid, 'fid' => $this->fid ) );
		}
		catch ( SPException $x ) {
			Sobi::Error( $this->name(), SPLang::e( 'CANNOT_DELETE_FIELD_DATA', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
		}
	}

	/**
	 * @return array
	 */
	public function struct()
	{
		SPLang::load( 'SpApp.download' );
		$file = $this->fileData();
		if( is_array( $file ) && count( $file ) ) {
			$this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData'; strlen( $this->cssClass );
			$this->cssClass = $this->cssClass.' '.$this->nid;

			/* handle permissions */
			$gids = array();
			$allowed = $this->checkPerms( $file, 'downloaders', $gids );
			
			if( $file[ 'license' ] ) {
				$licenses = $this->getLicenses( true, $file[ 'license' ] );
				$license = $licenses[ $file[ 'license' ] ];
			}
			else {
				$license = null;
			}
			if( $allowed ) {
				if( $license ) {
					SPFactory::header()->addJsFile( 'jquery' );
					SPFactory::header()->addJsFile( 'simplemodal' );
					SPFactory::header()->addCssFile( 'download' );
					SPFactory::header()->addJsFile( 'download' );
					$url = 'javascript:SPConfirmLicense( "'.$this->nid.'" )';
					$title = Sobi::Txt( 'DWLA_FE_DWN_LIC_TITLE', $license[ 'name' ] );
					$acc = Sobi::Txt( 'DWLA_FE_DWN_ACCEPT_LIC' );
					$dcc = Sobi::Txt( 'DWLA_FE_DWN_DECLINE_LIC' );
					$dcl = Sobi::Txt( 'Close' );
					$dbt = Sobi::Txt( 'DWLA_FE_DWN_GET_FILE', basename( $file[ 'abs_path' ] ) );
					$data[ 'div' ] = array(
						'_complex' => 1,
						'_xml' => true,
						'_attributes' => array( 'id' => "{$this->nid}_license", 'class' => 'osx-modal-data', 'style' => 'display:none;' ),
						'_data' => "
							<div id=\"{$this->nid}_license_title\" class=\"osx-modal-title\">
						    	<div id=\"{$this->nid}_license_close\" class=\"osx-close\"><a href=\"#\" class=\"simplemodal-close\">x</a></div>
								{$title}
							</div>
							<div id=\"{$this->nid}_license_data\" class=\"osx-license-content\">
								{$license[ 'text' ]}
								<div>
									<hr/>
									<form action=\"index.php\" method=\"post\">
										<div style=\"width:100%; text-align: right;\">
											<input type=\"checkbox\" value=\"{$this->_fData->fid}\" name=\"license_confirm\" onclick=\"SPEnableDwnl( '{$this->nid}', this )\"/>
											<label for=\"{$this->nid}_license_confirm\"><b>{$acc}</b></label>
											<input type=\"submit\" value=\"{$dbt}\" name=\"{$this->nid}_license_dwnl\" id=\"{$this->nid}_license_dwnl\" disabled=\"disabled\"  onclick=\"SPFinishDwnl( '{$this->nid}_license_decline', '{$dcl}' )\"/>
											<input type=\"button\" name=\"{$this->nid}_license_decline\" id=\"{$this->nid}_license_decline\" class=\"simplemodal-close\" value=\"{$dcc}\"/>
											<input type=\"hidden\" value=\"com_sobipro\" name=\"option\"/>
											<input type=\"hidden\" value=\"download.file\" name=\"task\"/>
											<input type=\"hidden\" value=\"raw\" name=\"format\"/>
											<input type=\"hidden\" value=\"{$this->_fData->fid}.{$this->_fData->sid}\" name=\"fid\"/>
											<input type=\"hidden\" value=\"".Sobi::Section()."\" name=\"sid\"/>
										</div>
									</form>
								</div>
							</div>
						",
					);
				}
				else {
					$url = Sobi::Url( array( 'task' => 'download.file', 'fid' => $this->_fData->fid.'.'.$this->_fData->sid, 'sid' => Sobi::Section() ) );
				}
			}
			else {
				$m = Sobi::Txt( 'JS.DWLA_FE_DWN_NO_PERMS' );
				$url = 'javascript:alert( \''.$m.'\' )';
			}
			$attr = array( 'class' => $this->cssClass.' spdownload', 'href' => $url, 'title' => Sobi::Txt( 'DWLA_FE_DWN_GET_FILE', basename( $file[ 'abs_path' ] ) ) );

			if( in_array( 'image', $this->displayAs ) ) {
				$data[ '_a_0' ] = array(
					'_complex' => 1,
					'_data' => array(
						'img' =>  array(
							'_complex' => 1,
							'_data' => null,
							'_attributes' => array(
								'class' => $this->cssClass.' spdownload',
								'src' => $file[ 'icon' ],
								'alt' => $file[ 'mime_type' ]
						 	)
						)
					),
					'_attributes' => $attr
				);
			}
			if( in_array( 'text', $this->displayAs ) ) {
				$data[ '_a_1' ] = array( '_complex' => 1, '_data' => basename( $file[ 'abs_path' ] ),  '_attributes' => $attr );
			}

			$count = SPFactory::db()
				->select( 
					array( 'count(*)' , 'filename' , 'sid' , 'fid', 'md5sum' ), 'spdb_downloads', 
					array( 'sid' => $this->_fData->sid, 'fid' => $this->_fData->fid ), null, 0, 0, false, 'md5sum' )
				->loadAssocList();
			$oCount = 0;
			$fCount = 0;
			if( count( $count ) ) {
				foreach ( $count as $sfile ) {
					$oCount += $sfile[ 'count(*)' ];
					if( $sfile[ 'md5sum' ] == $file[ 'checksum' ][ 'md5' ] ) {
						$fCount = $sfile[ 'count(*)' ];
					}
				}
			}
			$get = SPUser::availableGroups();
			//SPConfig::debOut( $gids );
			$groups = array();
			foreach ( $gids as $group ) {
				$groups[] = array( '_tag' => 'group', '_value' => $get[ $group ], '_id' => $group );
			}			
			return array(
				'_complex' => 1,
				'_data' => $data,
				'_xml_out' => array( 'downloaders' => $groups ),
				'_attributes' =>  array(
					'filename' => basename( $file[ 'abs_path' ] ),
					'mime_type' => $file[ 'mime_type' ],
					'size' => $file[ 'size' ],
					'upload_date' => $file[ 'upload_date' ],
					'license' => $license[ 'name' ],
					'count' => $fCount,
					'overall_count' => $oCount,
					'url'  =>  $url,
					'md5_checksum' => $file[ 'checksum' ][ 'md5' ],
					'sha1_checksum' => $file[ 'checksum' ][ 'sha1' ]
				)
			);
		}
	}
}