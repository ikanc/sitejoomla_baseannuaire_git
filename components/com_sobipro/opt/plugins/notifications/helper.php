<?php
/**
 * @version: $Id: helper.php 2518 2012-06-28 09:43:14Z Radek Suski $
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
 * $Date: 2012-06-28 11:43:14 +0200 (Do, 28 Jun 2012) $
 * $Revision: 2518 $
 * $Author: Radek Suski $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 */
abstract class SPNotificationHelper
{
    const debug = false;

    public static function Trigger( $action, $args, $triggers, $handlers = array() )
    {
        self::debug( "Incoming request. Action: {$action}", __FUNCTION__ );
        $action = preg_split( '/([A-Z][a-z]+)/', $action, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
        $subject = $action[ 0 ];
        array_shift( $action );
        $a = null;
        if ( count( $action ) ) {
            foreach ( $action as $p ) {
                $a = $a . $p;
            }
        }
        switch ( strtolower( $subject ) ) {
            case 'entry':
                $params = array();
                self::payment( $a, $args, $triggers[ 'payment' ], $params );
                self::entry( $a, $args, $triggers[ 'entry' ], $params );
                break;
            default:
                $customHandler = self::checkHandler( strtolower( $subject ), $handlers );
                if ( !( $customHandler ) ) {
                    self::custom( $a, $args, $triggers[ strtolower( $subject ) ] );
                }
                else {
                    self::customHandler( $a, $args, $triggers[ strtolower( $subject ) ], $customHandler, strtolower( $subject ) );
                }
                break;
        }
    }


    private static function customHandler( $action, $args, $triggers, $handler, $subject )
    {
        $messages = array();
        foreach ( $triggers as $trigger => $label ) {
            if ( strstr( $trigger, $action ) ) {
                $settings = array();
                if ( method_exists( $handler, 'prepareMessageSettings' ) ) {
                    $handler->prepareMessageSettings( $trigger, $settings, $args[ 0 ] );
                }
                else {
                    $settings = self::loadMessages( $trigger, $subject );
                }
                if ( count( $settings ) && self::messageEnabled( $settings[ 'enabled' ] ) ) {
                    if ( method_exists( $handler, 'prepareMessageArgs' ) ) {
                        $handler->prepareMessageArgs( $trigger, $settings, $args[ 0 ] );
                    }
                    foreach ( $settings as $k => $v ) {
                        if ( method_exists( $handler, 'parseMessage' ) ) {
                            $v = $handler->parseMessage( $trigger, $args[ 0 ] );
                        }
                        $messages[ $trigger ][ $k ] = self::parse( $v, $args[ 0 ] );
                    }
                }
            }
        }
        self::sendMessages( $messages );
    }

    private static function checkHandler( $subject, $handlers )
    {
        if ( isset( $handlers[ $subject ] ) ) {
            if ( SPLoader::translatePath( $handlers[ $subject ] ) ) {
                $handler = SPFactory::Instance( $handlers[ $subject ] );
                if ( method_exists( $handler, 'parseMessage' ) || method_exists( $handler, 'prepareMessageArgs' ) || method_exists( $handler, 'prepareMessageSettings' ) ) {
                    return $handler;
                }
            }
        }
        return false;
    }


    private static function payment( $action, &$args, $triggers, &$params )
    {
        if ( !( $args[ 0 ] instanceof SPEntry ) ) {
            $sid = $args[ 0 ];
        }
        else {
            $sid = $args[ 0 ]->get( 'id' );
        }
        $entry = SPFactory::Entry( $sid );
        $args[ 0 ] = $entry;
        $payment = SPFactory::payment()->summary( $entry->get( 'id' ) );
        $methods = array();
        $HTMLmethods = array();
        Sobi::Trigger( 'AppPaymentMessage', 'Send', array( &$methods, $args[ 0 ], &$payment ) );
        Sobi::Trigger( 'AppPaymentMessage', 'Send', array( &$HTMLmethods, $args[ 0 ], &$payment, true ) );
        $payment[ 'methods' ] = $methods;
        $payment[ 'html_methods' ] = $HTMLmethods;
        $params = array(
            'entry' => $entry,
            'user' => SPFactory::user(),
            'payment' => $payment,
            'author' => SPFactory::Instance( 'cms.base.user', $args[ 0 ]->get( 'owner' ) )
        );
        if ( $action == 'AfterSave' && SPFactory::payment()->count( $entry->get( 'id' ) ) ) {
            $messages = array();
            foreach ( $triggers as $trigger => $label ) {
                $settings = self::loadMessages( $trigger, 'payment' );
                if ( !( count( $settings ) ) ) {
                    // in case we still have some old stuff
                    $settings = self::loadMessages( $trigger );
                }
                if ( count( $settings ) && self::messageEnabled( $settings[ 'enabled' ] ) ) {
                    foreach ( $settings as $k => $v ) {
                        $messages[ $trigger ][ $k ] = self::parse( $v, $params );
                    }
                    $messages[ $trigger ][ 'sid' ] = $entry->get( 'id' );
                }
            }
            self::sendMessages( $messages );
        }
    }

    private static function entry( $action, $args, $triggers, &$params )
    {
        $messages = array();
        $state = 0;
        /* get the right message id */
        if ( $action == 'AfterChangeState' ) {
            foreach ( $triggers as $trigger => $label ) {
                if ( !( strstr( $trigger, $action . ( $args[ 1 ] ? '.on' : '.off' ) ) ) ) {
                    unset( $triggers[ $trigger ] );
                }
            }
        } /* if the action is "Un-Approve" entry - do nothing */
        elseif ( $action == 'AfterApprove' && !( $args[ 1 ] ) ) {
            return true;
        }
        /* determine if saving a new entry or updating an existing one */
        if ( ( SPRequest::sid() || SPRequest::int( 'entry_id', 'post' ) ) && ( $args[ 0 ]->get( 'updatedTime' ) != $args[ 0 ]->get( 'createdTime' ) ) ) {
            $action = str_replace( 'Save', 'Update', $action );
        }
        foreach ( $triggers as $trigger => $label ) {
            if ( strstr( $trigger, $action ) ) {
                // this is the new method with subject
                $settings = self::loadMessages( $trigger, 'entry' );
                if ( !( count( $settings ) ) ) {
                    // in case we still have some old stuff
                    $settings = self::loadMessages( $trigger );
                }
                if ( count( $settings ) && self::messageEnabled( $settings[ 'enabled' ] ) ) {
                    foreach ( $settings as $k => $v ) {
                        $messages[ $trigger ][ $k ] = self::parse( $v, $params );
                    }
                    $messages[ $trigger ][ 'sid' ] = $args[ 0 ]->get( 'id' );
                }
            }
        }
        self::sendMessages( $messages );
    }

    private static function parse( $txt, $params )
    {
//		self::debug( "Parsing text. {$txt}", __FUNCTION__ );
        preg_match_all( '/{([a-zA-Z0-9\-_\:\.\[\],]+)}/', $txt, $placeHolders );
        if ( count( $placeHolders[ 1 ] ) ) {
            SPLang::load( 'SpApp.notifications' );
            foreach ( $placeHolders[ 1 ] as $placeHolder ) {
                /* handle special application placeholders */
                $replacement = null;
                switch ( $placeHolder ) {
                    case 'entry.url':
                        if ( isset( $params[ 'entry' ] ) ) {
                            $replacement = Sobi::Url( array( 'title' => $params[ 'entry' ]->get( 'name' ), 'sid' => $params[ 'entry' ]->get( 'id' ) ), false, true, true, true );
                        }
                        break;
                    case 'entry.edit_url':
                        if ( isset( $params[ 'entry' ] ) ) {
                            $replacement = Sobi::Url( array( 'task' => 'entry.edit', 'sid' => $params[ 'entry' ]->get( 'id' ) ), false, true, true, true );
                        }
                        break;
                    case 'entry.state':
                        if ( isset( $params[ 'entry' ] ) ) {
                            $replacement = ( $params[ 'entry' ]->get( 'state' ) == 0 ) ? Sobi::Txt( 'NOTA.ENTRY_DISABLED' ) : Sobi::Txt( 'NOTA.ENTRY_ENABLED' );
                        }
                        break;
                    case 'entry.approval':
                        if ( isset( $params[ 'entry' ] ) ) {
                            $replacement = ( $params[ 'entry' ]->get( 'approved' ) == 0 ) ? Sobi::Txt( 'NOTA.ENTRY_UNAPPROVED' ) : Sobi::Txt( 'NOTA.ENTRY_APPROVED' );
                        }
                        break;
                    case 'payment.methods.out':
                        if ( isset( $params[ 'payment' ] ) && isset( $params[ 'payment' ][ 'methods' ] ) ) {
                            foreach ( $params[ 'payment' ][ 'methods' ] as $method ) {
                                $replacement .= $method[ 'title' ] . '<hr/>';
                                $replacement .= $method[ 'content' ];
                            }
                        }
                        break;
                    case 'payment.methods.html':
                        if ( isset( $params[ 'payment' ] ) && isset( $params[ 'payment' ][ 'html_methods' ] ) ) {
                            foreach ( $params[ 'payment' ][ 'html_methods' ] as $method ) {
                                $replacement .= $method[ 'title' ] . '<hr/>';
                                $replacement .= $method[ 'content' ];
                            }
                        }
                        break;
                    default:
                        if ( strstr( $placeHolder, 'payment.columns' ) && isset( $params[ 'payment' ][ 'positions' ] ) && count( $params[ 'payment' ][ 'positions' ] ) ) {
                            preg_match_all( '/:\[([a-zA-Z0-9,]+)\]/', $placeHolder, $cols );
                            if ( count( $cols[ 1 ] ) ) {
                                $cols = explode( ',', $cols[ 1 ][ 0 ] );
                                if ( count( $cols ) ) {
                                    $replacement .= "\n" . '<div style="min-width: 600px;">';
                                    $w = 100 / count( $cols );
                                    foreach ( $cols as $col ) {
                                        $replacement .= "\n\t" . '<div style="width:' . $w . '%; float:left; font-weight:bold; text-align: center;">';
                                        $replacement .= Sobi::Txt( 'NOTA.PAYMENT_' . strtoupper( $col ) );
                                        $replacement .= "\n\t" . '</div>';
                                    }
                                    $replacement .= "\n\t" . '<div style="clear:both;"></div>';
                                    foreach ( $params[ 'payment' ][ 'positions' ] as $data ) {
                                        if ( count( $data ) ) {
                                            foreach ( $cols as $col ) {
                                                $replacement .= "\n\t" . '<div style="width:' . $w . '%; float:left; text-align: center;">';
                                                if ( isset( $data[ $col ] ) ) {
                                                    $replacement .= $data[ $col ];
                                                }
                                                $replacement .= /*"\n\t".*/
                                                        '</div>';
                                            }
                                            $replacement .= "\n\t" . '<div style="clear:both;"></div>';
                                        }
                                    }
                                    $replacement .= "\n" . '</div>';
                                }
                            }
                        }
                        break;
                }
                if ( strlen( $replacement ) ) {
                    $txt = str_replace( '{' . $placeHolder . '}', ( string )$replacement, $txt );
                }
            }
        }
        return preg_replace( '/{([a-zA-Z0-9\-_\:\.\[\],]+)}/', null, SPLang::replacePlaceHolders( $txt, $params ) );
    }

    private static function custom( $action, $args, $triggers )
    {
        $messages = array();
        foreach ( $triggers as $trigger => $label ) {
            if ( strstr( $trigger, $action ) ) {
                $settings = self::loadMessages( $trigger );
                if ( count( $settings ) && self::messageEnabled( $settings[ 'enabled' ] ) ) {
                    foreach ( $settings as $k => $v ) {
                        $messages[ $trigger ][ $k ] = self::parse( $v, $args[ 0 ] );
                    }
                }
            }
        }
        self::sendMessages( $messages );
    }

    private function stripTags( $txt )
    {
        $txt = str_replace( '<hr/>', "\n-------------------------------------\n", $txt );
        $txt = str_replace( '<li>', "\n  - ", $txt );
        $txt = str_replace( '</div>', "\n", $txt );
        $txt = str_replace( '</p>', "\n", $txt );
        return strip_tags( $txt );
    }

    private static function messageEnabled( $setting )
    {
        return ( defined( 'SOBIPRO_ADM' ) ) ? ( ( $setting == 1 ) || ( $setting == 3 ) ) : ( $setting == 1 ) || ( $setting == 2 );
    }

    private static function sendMessages( $messages )
    {
        if ( count( $messages ) ) {
            // @todo: see below
//            jimport( 'joomla.mail.mail' );
            self::debug( $messages, __FUNCTION__ );
            foreach ( $messages as $ident => $message ) {
                $send = false;
                $err = 'none';
                if ( !( $message[ 'html' ] ) ) {
                    $message[ 'body' ] = self::stripTags( $message[ 'body' ] );
                }
                if ( self::validMail( $message[ 'fromMail' ] ) && self::validMail( $message[ 'toMail' ] ) ) {
                    // @todo: it has been fixed after RC1 so in the next releas we can useit again
                    $mail = SPFactory::Instance( 'services.mail' );
                    //$mail = new JMail();
                    $mail->setSender( array( $message[ 'fromMail' ], $message[ 'from' ] ) );
                    $mail->setSubject( $message[ 'subject' ] );
                    $mail->setBody( $message[ 'body' ] );
                    // doesn't works in J16 - no idea why
                    // $mail->addRecipient( ( strlen( $message[ 'to' ] ) ? "{$message[ 'to' ]} <{$message[ 'toMail' ]}>" : $message[ 'toMail' ] ) );
                    $mail->AddAddress( $message[ 'toMail' ], $message[ 'to' ] );
                    $mail->IsHTML( $message[ 'html' ] );
                    $mail->addCC( explode( ',', $message[ 'cc' ] ) );
                    $mail->addBCC( explode( ',', $message[ 'bcc' ] ) );
                    $send = $mail->Send();
                    if ( $send instanceof Exception ) {
                        $err = $send->getMessage();
                        $send = false;
                    }
                    else {
                        $send = true;
                    }
                    unset( $mail );
                }
                else {
                    $err = SPLang::e( 'NO_VALID_EMAIL_ADDRESS_FOUND' );
                    Sobi::Error( 'Notifications', $err, SPC::WARNING, 0, __LINE__, __FILE__ );
                }
                SPFactory::db()
                        ->insert(
                    'spdb_notifications',
                    array(
                        'mid' => null,
                        'sid' => ( isset( $message[ 'sid' ] ) ? $message[ 'sid' ] : 0 ),
                        'mailFrom' => $message[ 'fromMail' ],
                        'mailFromName' => $message[ 'from' ],
                        'mailTo' => $message[ 'toMail' ],
                        'mailToName' => $message[ 'to' ],
                        'mailSubject' => $message[ 'subject' ],
                        'mailBody' => $message[ 'body' ],
                        'mailCC' => $message[ 'cc' ],
                        'mailBCC' => $message[ 'bcc' ],
                        'mailDate' => 'FUNCTION:NOW()',
                        'mailHTML' => $message[ 'html' ],
                        'error' => $err,
                        'send' => $send,
                        'uid' => Sobi::My( 'id' ),
                        'pid' => Sobi::Section(),
                    )
                );
            }
        }

    }

    private function validMail( $mail )
    {
        return preg_match( '/[a-z0-9\.\-\_]+@[a-z0-9\.\-\_]{2,}\.[a-z]{2,5}/i', $mail );
    }

    private static function loadMessages( $nid, $subject = null )
    {
        static $data = array();
        static $setting = array();
        if ( $subject ) {
            $nid = $subject . '.' . $nid;
        }
        if ( isset( $data[ $nid ] ) ) {
            return $data[ $nid ];
        }
        self::debug( "Loading messages. Nid: {$nid}", __FUNCTION__ );
        SPFactory::registry()->loadDBSection( 'notifications' );
        $setting = Sobi::Reg( 'notifications.settings.params' );
        if ( strlen( $setting ) ) {
            $setting = SPConfig::unserialize( $setting );
        }
        self::debug( $setting, __FUNCTION__ );

        if ( is_array( $setting ) && isset( $setting[ Sobi::Section() ] ) && isset( $setting[ Sobi::Section() ][ $nid ] ) ) {
            $setting = $setting[ Sobi::Section() ][ $nid ];
        }
        elseif ( is_array( $setting ) && isset( $setting[ -1 ] ) && isset( $setting[ -1 ][ $nid ] ) ) {
            $setting = $setting[ -1 ][ $nid ];
        }

        $ids = array(
            'from' => '{cfg:mail.fromname}',
            'fromMail' => '{cfg:mail.from}',
            'to' => '{user.name}',
            'toMail' => '{user.email}',
            'subject' => 'Message Subject',
            'body' => 'Message Body',
            'cc' => null,
            'bcc' => null,
            'html' => true,
            'enabled' => false
        );
        foreach ( $ids as $id => $v ) {
            $value = SPLang::getValue( $nid . '.' . $id, 'application', Sobi::Section() );
            if ( !( strlen( $value ) ) ) {
                $value = SPLang::getValue( $nid . '.' . $id, 'application', -1 );
            }
            if ( strlen( $value ) ) {
                $ids[ $id ] = $value;
            }
            else {
                if ( isset( $setting[ $id ] ) ) {
                    $ids[ $id ] = $setting[ $id ];
                }
                else {
                    $ids[ $id ] = $v;
                }
            }
        }
        self::debug( $ids, __FUNCTION__ );
        $data[ $nid ] = $ids;
        return $ids;
    }

    private static function debug( $msg, $f )
    {
        if ( self::debug ) {
            SPConfig::debOut( '[ ' . date( DATE_RFC822 ) . ' ] [ ' . $f . ' ] ', false, false, true );
            SPConfig::debOut( $msg, false, false, true );
        }
    }
}
