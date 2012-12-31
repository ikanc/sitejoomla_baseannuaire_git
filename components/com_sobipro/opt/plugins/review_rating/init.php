<?php
/**
 * @version: $Id: init.php 1983 2011-11-09 17:41:31Z Radek Suski $
 * @package: SobiPro Review & Rating Application
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2010 Sigsiu.NET (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2011-11-09 18:41:31 +0100 (Mi, 09 Nov 2011) $
 * $Revision: 1983 $
 * $Author: Radek Suski $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 */
class SPRr extends SPApplication
{
	private static $methods = array( 'CreateAdmMenu', 'EntryViewDetails', 'ListEntry', 'AfterDisplayEntryAdmView' );
	private $triggers = array();

	public function __construct()
	{
		self::$methods = array_unique( self::$methods );
	}

	public function AfterDisplayEntryAdmView()
	{
//		echo "<div style='clear:both'>&nbsp;</div>";
	}

	/* (non-PHPdoc)
	 * @see Site/lib/plugins/SPPlugin#provide($action)
	 */
	public function provide( $action )
	{
		// when loaded
		static $lang = false;
		if( !( $lang ) && class_exists( 'SPLang' ) ) {
			SPLang::load( 'SpApp.review_rating' );
			$lang = true;
		}
		return in_array( $action, self::$methods );
	}

	public function CreateAdmMenu( &$menu )
	{
		if( ( Sobi::Section() ) ) {
			$this->CreateMenu( $menu );
		}
	}

	private function CreateMenu( &$menu )
	{
		if( isset( $menu[ 'AMN.SEC_CFG' ] ) ) {
			$menu[ 'AMN.SEC_CFG' ][ 'review' ] = self::Txt( 'MENU_SPRR' );
		}
		if( isset( $menu[ 'AMN.ENT_CAT' ] ) ) {
			$menu[ 'AMN.ENT_CAT' ][ 'review.list' ] = self::Txt( 'MENU_SPRR_ALL' );
		}
	}

	public function EntryViewDetails( &$data )
	{
		SPFactory::header()->addCssFile( array( 'review' , 'jquery-ui.'.Sobi::Cfg( 'jquery.ui_theme', 'smoothness.smoothness' ) ) );
		SPFactory::header()->addJsFile( array( 'jquery', 'rating', 'review', 'jquery-ui' ) );
		$site = SPRequest::int( 'site', 1 );
		SPFactory::Model( 'review' )
			->setSid( $data[ 'entry' ][ '_attributes' ][ 'id' ] )
			->setDetails( $data, $site );
	}

	public function ListEntry( &$data )
	{
		SPFactory::header()->addCssFile( array( 'review' , 'jquery-ui.'.Sobi::Cfg( 'jquery.ui_theme', 'smoothness.smoothness' ) ) );
		SPFactory::Model( 'review' )
			->setSid( $data[ 'id' ] )
			->setList( $data );
	}

	public static function Txt( $txt )
	{
		return Sobi::Txt( 'SPRRA.'. $txt );
	}
}
?>