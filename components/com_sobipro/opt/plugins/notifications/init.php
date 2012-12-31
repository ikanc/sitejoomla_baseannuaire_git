<?php
/**
 * @version: $Id: init.php 2512 2012-06-27 16:30:20Z Radek Suski $
 * @package: SobiPro Notifications Application
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2012 Sigsiu.NET (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2012-06-27 18:30:20 +0200 (Mi, 27 Jun 2012) $
 * $Revision: 2512 $
 * $Author: Radek Suski $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 */
class SPNotifications extends SPApplication
{
    private static $methods = array( 'CreateAdmMenu' );
    private $triggers = array();
    private static $parsers = array();

    public function __construct()
    {
        // load std triggers
        $this->triggers = SPLoader::loadIniFile( 'etc.notifications' );
        $custom = SPLoader::translateDirPath( 'etc.napp_cfg' );
        if ( $custom ) {
            self::custom( $custom, self::$methods, $this->triggers );
        }
        if ( count( $this->triggers ) ) {
            foreach ( $this->triggers as $subject => $triggers ) {
                $subject = ucfirst( $subject );
                if ( count( $triggers ) ) {
                    foreach ( $triggers as $trigger => $label ) {
                        $trigger = explode( '.', $trigger );
                        self::$methods[ ] = $subject . $trigger[ 0 ];
                    }
                }
            }
        }
//        SPConfig::debOut(self::$methods);
        self::$methods = array_unique( self::$methods );
    }

    public static function custom( $path, &$methods, &$triggers, $full = false )
    {
        $defs = SPFactory::Instance( 'base.fs.directory', $path )->searchFile( '.ini', false, 1 );
        if ( count( $defs ) ) {
            foreach ( $defs as $file ) {
                $def = parse_ini_file( $file->getName(), true );
                $handler = isset( $def[ 'config' ][ 'handler' ] ) ? $def[ 'config' ][ 'handler' ] : null;
                if ( $full && isset( $def[ 'config' ][ 'language' ] ) ) {
                    SPLang::load( $def[ 'config' ][ 'language' ] );
                }
                // silent triggers - processed fully by a custom handler
                if ( isset( $def[ 'config' ][ 'triggers' ] ) ) {
                    $sTriggers = explode( ',', $def[ 'config' ][ 'triggers' ] );
                    foreach ( $sTriggers as $trigger ) {
                        $trigger = trim( $trigger );
                        if ( !( $full ) ) {
                            $sTrigger = explode( '.', $trigger );
                            $subject = trim( strtolower( $sTrigger[ 0 ] ) );
                            self::$parsers[ $subject ] = $handler;
                            $methods[ ] = ucfirst( $subject ) . ucfirst( $sTrigger[ 1 ] );
                            $triggers[ $subject ][ $trigger ] = 'silent';
                        }
                    }
                }
                else {
                    unset( $def[ 'config' ] );
                    foreach ( $def as $subject => $t ) {
                        foreach ( $t as $trigger => $label ) {
                            if ( !( $full ) ) {
                                self::$parsers[ $subject ] = $handler;
                                $strigger = explode( '.', $trigger );
                                $methods[ ] = ucfirst( $subject ) . $strigger[ 0 ];
                                $triggers[ $subject ][ $trigger ] = $label;
                            }
                            else {
                                $methods[ $subject ][ $trigger ] = $label;
                                $triggers[ $subject ][ $trigger ] = $label;
                            }
                        }
                    }
                }
            }
        }
        return self::$parsers;
    }

    public function __call( $method, $args )
    {
        if ( in_array( $method, self::$methods ) ) {
            SPLoader::loadClass( 'notifications.helper', false, 'application' );
            SPNotificationHelper::Trigger( $method, $args, $this->triggers, self::$parsers );
        }
    }

    /* (non-PHPdoc)
     * @see Site/lib/plugins/SPPlugin#provide($action)
     */
    public function provide( $action )
    {
        return in_array( $action, self::$methods );
    }

    public function CreateAdmMenu( &$menu )
    {
        if ( ( Sobi::Section() ) ) {
            $this->CreateMenu( $menu );
        }
    }

    private function CreateMenu( &$menu )
    {
        if ( isset( $menu[ 'AMN.SEC_CFG' ] ) ) {
            $gConf = $menu[ 'AMN.SEC_CFG' ];
            $ngConf = array();
            foreach ( $gConf as $task => $entry ) {
                SPLang::load( 'SpApp.notifications' );
                $ngConf[ $task ] = $entry;
                if ( $task == 'config.general' ) {
                    $ngConf[ 'notifications' ] = Sobi::Txt( 'NOTA.MENU_NOTIFICATIONS' );
                }
            }
            $menu[ 'AMN.SEC_CFG' ] = $ngConf;
            if( isset( $menu[ 'AMN.ENT_CAT' ] ) ) {
                $menu[ 'AMN.ENT_CAT' ][ 'notifications.messages' ] = Sobi::Txt( 'NOTA.MENU_NOTIFICATIONS_MSGS' );
          	}
        }
    }
}
