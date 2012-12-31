<?php
/**
 * @version: $Id: notifications.php 2521 2012-06-28 10:54:10Z Radek Suski $
 * @package: SobiPro Notifications Application
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2012 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2012-06-28 12:54:10 +0200 (Do, 28 Jun 2012) $
 * $Revision: 2521 $
 * $Author: Radek Suski $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

SPLoader::loadController( 'config', true );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 22-Jun-2010 15:55:21
 */
class SPNotificationsCtrl extends SPConfigAdmCtrl
{
    /**
     * @var string
     */
    protected $_type = 'notifications';
    /**
     * @var string
     */
    protected $_defTask = 'list';

    private $triggers = array();
    private $parsers = array();

    public function execute()
    {
        $this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
        SPLang::load( 'SpApp.notifications' );
        $helpTask = 'notifications.' . $this->_task;
        SPFactory::registry()->set( 'task', $helpTask );

        switch ( $this->_task ) {
            case 'list':
                $this->nList();
                Sobi::ReturnPoint();
                break;
            case 'messages':
                $this->que();
                Sobi::ReturnPoint();
                break;
            case 'message':
                $this->message();
                break;
            case 'edit':
                $this->edit();
                break;
            case 'resend':
                $this->resend();
                break;
            case 'save':
            case 'apply':
                $this->saveTpl( $this->_task == 'apply' );
                break;
            case 'cancel':
                Sobi::Redirect( SPMainFrame::getBack() );
                break;
            case 'delete':
                $this->deleteMsg();
                break;
            default:
                if ( !( parent::execute() ) ) {
                    Sobi::Error( 'SPUsersCtrl', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
                }
                break;
        }
    }

    private function resend()
    {
        if ( !( SPFactory::mainframe()->checkToken() ) ) {
            Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
        }
        $err = null;
        $sent = false;
        $message = array(
            'mid' => SPRequest::int( 'mid', 0, 'post' ),
            'sid' => SPRequest::int( 'tsid', 0, 'post' ),
            'uid' => Sobi::My( 'id' ),
            'fromMail' => SPRequest::string( 'mailFrom', null, false, 'post' ),
            'from' => SPRequest::string( 'mailFromName', null, false, 'post' ),
            'toMail' => SPRequest::string( 'mailTo', null, false, 'post' ),
            'to' => SPRequest::string( 'mailToName', null, false, 'post' ),
            'subject' => SPRequest::string( 'mailSubject', null, false, 'post' ),
            'body' => SPRequest::string( 'mailBody', null, true, 'post' ),
            'cc' => SPRequest::string( 'mailCC', null, false, 'post' ),
            'bcc' => SPRequest::string( 'mailBCC', null, false, 'post' ),
            'html' => SPRequest::int( 'mailHTML', 0, 'post' ),
        );
        $mail = SPFactory::Instance( 'services.mail' );
        $mail->setSender( array( $message[ 'fromMail' ], $message[ 'from' ] ) );
        $mail->setSubject( $message[ 'subject' ] );
        $mail->setBody( $message[ 'body' ] );
        $mail->AddAddress( $message[ 'toMail' ], $message[ 'to' ] );
        $mail->IsHTML( $message[ 'html' ] );
        $mail->addCC( explode( ',', $message[ 'cc' ] ) );
        $mail->addBCC( explode( ',', $message[ 'bcc' ] ) );
        $send = $mail->Send();

        if ( $send instanceof Exception ) {
            $err = $send->getMessage();
        }
        else {
            $sent = true;
        }
        SPFactory::db()
                ->update( 'spdb_notifications',
            array(
                'sid' => ( isset( $message[ 'sid' ] ) ? $message[ 'sid' ] : 0 ),
                'uid' => Sobi::My( 'id' ),
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
                'send' => $sent,
            ),
            array( 'mid' => $message[ 'mid' ] )
        );
        if ( $sent ) {
            Sobi::Redirect( Sobi::Back(), Sobi::Txt( 'NOTA.RESEND_SENT' ) );
        }
        else {
            Sobi::Redirect( Sobi::Back(), Sobi::Txt( 'NOTA.RESEND_NOT_SENT', $err ), SPC::ERROR_MSG );
        }
    }

    private function message()
    {
        $mid = SPRequest::int( 'mid' );
        SPLoader::loadClass( 'html.tooltip' );
        $message = SPFactory::db()
                ->select( '*', 'spdb_notifications', array( 'mid' => $mid ) )
                ->loadObject();

        if ( $message->send ) {
            $status = SPTooltip::toolTip( Sobi::Txt( 'NOTA.MESSAGE_STATUS_SENT' ), Sobi::Txt( 'NOTA.MESSAGE_STATUS_HEADER' ), Sobi::Cfg( 'list_icons.published' ) );
        }
        else {
            $status = SPTooltip::toolTip( Sobi::Txt( 'NOTA.MESSAGE_STATUS_NOT_SENT' ) . ': ' . $message->error, Sobi::Txt( 'NOTA.MESSAGE_STATUS_HEADER' ), Sobi::Cfg( 'list_icons.unpublished' ) );
        }
        if ( $message->uid ) {
            $userUrl = SPUser::userUrl( $message->uid );
            $user = SPUser::getBaseData( $message->uid );
            $user = '<a href="' . $userUrl . '">' . $user[ $message->uid ]->name . '</a>';
        }
        else {
            $user = Sobi::Txt( 'NOTA.EDIT_MAIL_SENDER_GUEST' );
        }

        $view = $this->getView( 'notifications' );
        $entry = SPFactory::Entry( $message->sid );
        $eUrl = Sobi::Url( array( 'sid' => $message->sid ), false, true, true );
        $view->assign( $this->_task, 'task' );
        $view->loadConfig( 'config.message' );
        $view->setTemplate( 'config.message' );
        $view->assign( $message, 'message' );
        $view->assign( $entry, 'entry' );
        $view->assign( $eUrl, 'url' );
        $view->assign( $status, 'status' );
        $view->assign( $user, 'author' );
        $view->addHidden( $message->sid, 'tsid' );
//        $view->addHidden( $message->mailHTML, 'mailHTML' );
        $view->display();
    }

    private function deleteMsg()
    {
        $mids = SPRequest::arr( 'mid' );
        if ( count( $mids ) ) {
            SPFactory::db()->delete( 'spdb_notifications', array( 'mid' => $mids ) );
            $msg = Sobi::Txt( 'NOTA.MSG_DELETED' );
        }
        else {
            $msg = Sobi::Txt( 'NOTA.MSG_NOT_DELETED' );
        }
        Sobi::Redirect( SPMainFrame::getBack(), $msg );
    }

    private function que()
    {
        $eLimit = Sobi::GetUserState( 'adm.messages.limit', 'elimit', Sobi::Cfg( 'adm_list.entries_limit', 25 ) );
        $eLimStart = SPRequest::int( 'eLimStart', 0 );
        $LimStart = $eLimStart ? ( ( $eLimStart - 1 ) * $eLimit ) : $eLimStart;
        $eCount = 0;
        $mOrder = Sobi::GetUserState( 'adm.messages.uorder', 'uorder', 'mailDate.desc' );
        $mOrder = str_replace( 'status', 'send', $mOrder );
        $view = $this->getView( 'notifications' );
        $db = SPFactory::db();
        $filter = Sobi::GetUserState( 'messages_filter', 'sp_messages_filter', null );
        $sections = Sobi::Cfg( 'notifications.legacy', false ) ? array( Sobi::Section(), 0 ) : array( Sobi::Section() );
        if ( strlen( $filter ) ) {
            $where = array(
                'mailFromName' => "%{$filter}%",
                'mailFrom' => "%{$filter}%",
                'mailToName' => "%{$filter}%",
                'mailTo' => "%{$filter}%",
                'mailSubject' => "%{$filter}%",
                'mailBody' => "%{$filter}%"
            );
            $where = $db->where( $where, 'OR' );
        }
        else {
            $where = array( 'pid' => $sections );
        }
        try {
            $eCount = $db->select( 'COUNT(*)', 'spdb_notifications', $where )->loadResult();
        } catch ( SPException $x ) {
        }
        if ( $eLimit == -1 ) {
            $eLimit = $eCount;
        }
        try {
            $results = $db->select( '*', 'spdb_notifications', $where, $mOrder, $eLimit, $LimStart )
                    ->loadAssocList();
        } catch ( SPException $x ) {
            Sobi::Error( $this->name(), SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
        }
        $messages = array();
        if ( count( $results ) ) {
            SPLoader::loadClass( 'html.tooltip' );
            $up = Sobi::Cfg( 'list_icons.unpublished' );
            $pu = Sobi::Cfg( 'list_icons.published' );

            foreach ( $results as $i => $result ) {
                if ( strlen( $filter ) && !( in_array( $result[ 'pid' ], $sections ) ) ) {
                    unset( $results[ $i ] );
                    continue;
                }
                $result[ 'id' ] = $result[ 'mid' ];
                $row = new SPObject();
                $row->castArray( $result );
                $sUrl = Sobi::Url( array( 'task' => 'entry.edit', 'sid' => $row->get( 'sid' ) ) );
                $mUrl = Sobi::Url(
                    array(
                        'task' => 'notifications.message',
                        'sid' => Sobi::Section(),
                        'mid' => $result[ 'mid' ]
                    )
                );
                if ( $row->get( 'send' ) ) {
                    $status = SPTooltip::toolTip( Sobi::Txt( 'NOTA.MESSAGE_STATUS_SENT' ), Sobi::Txt( 'NOTA.MESSAGE_STATUS_HEADER' ), $pu );
                }
                else {
                    $status = SPTooltip::toolTip( Sobi::Txt( 'NOTA.MESSAGE_STATUS_NOT_SENT' ) . ': ' . $row->get( 'error' ), Sobi::Txt( 'NOTA.MESSAGE_STATUS_HEADER' ), $up );
                }
                if ( $row->get( 'uid' ) ) {
                    $user = SPUser::userUrl( $row->get( 'uid' ) );
                    $user = '<a href="' . $user . '">' . $row->get( 'mailFromName' ) . '</a>';
                }
                else {
                    $user = $row->get( 'mailFromName' );
                }
                $messages[ ] = array(
                    'checkbox' => SPLists::checkedOut( $row, 'mid' ),
                    'sid' => '<a href="' . $sUrl . '">' . $row->get( 'sid' ) . '</a>',
                    'mailFromName' => $user,
                    'mailFrom' => $row->get( 'mailFrom' ),
                    'mailToName' => $row->get( 'mailToName' ),
                    'mailTo' => $row->get( 'mailTo' ),
                    'mailSubject' => '<a href="' . $mUrl . '">' . $row->get( 'mailSubject' ) . '</a>',
                    'mailDate' => $row->get( 'mailDate' ),
                    'status' => $status
                );
            }
        }
        $view->assign(
            SPLists::tableHeader(
                array(
                    'checkbox' => SP_TBL_HEAD_SELECTION_BOX,
                    'sid' => SP_TBL_HEAD_SORTABLE,
                    'mailFrom' => SP_TBL_HEAD_SORTABLE,
                    'mailTo' => SP_TBL_HEAD_SORTABLE,
                    'mailSubject' => SP_TBL_HEAD_SORTABLE,
                    'mailDate' => SP_TBL_HEAD_SORTABLE,
                    'status' => SP_TBL_HEAD_SORTABLE,
                ),
                'notifications', 'mid', 'uorder'
            ),
            'header'
        );
        $pageNav = SPLoader::loadClass( 'helpers.adm.pagenav' );
        $pageNav = new $pageNav( $eLimit, $eCount, $eLimStart, 'SPEntriesPageNav', 'elimit', 'SPEntriesPageLimit' );
        $view->assign( $pageNav->display( true ), 'messages_page_nav' );
        $view->assign( $this->_task, 'task' );
        $view->assign( $filter, 'filter' );
        $view->loadConfig( 'config.messages' );
        $view->setTemplate( 'config.messages' );
        $view->assign( $messages, 'messages' );
        $view->addHidden( $eLimStart, 'eLimStart' );
        $menu = $view->get( 'menu' );
        $menu->setOpen( 'AMN.ENT_CAT' );
        $view->assign( $menu, 'menu' );
        $view->display();
    }

    private function saveTpl( $apply )
    {
        if ( !( SPFactory::mainframe()->checkToken() ) ) {
            Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
        }
        $nid = SPRequest::cmd( 'nid' );
        $ident = str_replace( '.', '_', $nid );
        $values = SPRequest::search( $ident );

        SPFactory::registry()->loadDBSection( 'notifications' );
        $settings = Sobi::Reg( 'notifications.settings.params' );
        if ( strlen( $settings ) ) {
            $settings = SPConfig::unserialize( $settings );
        }
        if ( !( is_array( $settings ) ) ) {
            $settings = array( Sobi::Section() => array() );
        }
        if ( !( isset( $settings[ Sobi::Section() ] ) ) ) {
            $settings[ Sobi::Section() ] = array();
        }
        if ( !( isset( $settings[ Sobi::Section() ][ $nid ] ) ) ) {
            $settings[ Sobi::Section() ][ $nid ] = array();
        }

        /**
         * what the hell was this one for??
         * I think I'm getting older
         */
        $lang = array(
            'subject' => $values[ $ident . '_subject' ],
            'body' => $values[ $ident . '_body' ],
        );
        $settings[ Sobi::Section() ][ $nid ] = array(
            'from' => $values[ $ident . '_from' ],
            'fromMail' => $values[ $ident . '_fromMail' ],
            'to' => $values[ $ident . '_to' ],
            'toMail' => $values[ $ident . '_toMail' ],
            'cc' => $values[ $ident . '_cc' ],
            'bcc' => $values[ $ident . '_bcc' ],
            'html' => $values[ $ident . '_html' ],
            'enabled' => $values[ $ident . '_enabled' ],
        );
        SPFactory::registry()->saveDBSection( array( array( 'key' => 'settings', 'params' => $settings, 'value' => date( DATE_RFC822 ) ) ), 'notifications' );
        try {
            $data = array(
                'key' => str_replace( '_', '.', $ident . '_subject' ),
                'value' => $values[ $ident . '_subject' ],
                'type' => 'application',
                'id' => Sobi::Section(),
                'section' => Sobi::Section()
            );
            SPLang::saveValues( $data );
            $data[ 'key' ] = str_replace( '_', '.', $ident . '_body' );
            $data[ 'value' ] = $values[ $ident . '_body' ];
            SPLang::saveValues( $data );
        } catch ( SPException $x ) {
            $msg = SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() );
            Sobi::Error( 'SPNotificationsCtrl', $msg, SPC::WARNING, 0, __LINE__, __FILE__ );
            Sobi::Redirect( SPMainFrame::getBack(), $msg, SPC::ERROR_MSG, true );
        }
        if ( $apply ) {
            Sobi::Redirect(
                Sobi::Url(
                    array(
                        'task' => 'notifications.edit',
                        'nid' => $nid,
                        'sid' => Sobi::Section()
                    )
                ),
                Sobi::Txt( 'NOTA.MSG_TPL_SAVED' ) );
        }
        else {
            Sobi::Redirect( SPMainFrame::getBack(), Sobi::Txt( 'NOTA.MSG_TPL_SAVED' ) );
        }
    }

    private function edit()
    {
        $nid = SPRequest::cmd( 'nid' );
        $sub = $nidt = $ids  = null;
        $this->getIds( $nid, $sub, $nidt, $ids );
        $title = $title = Sobi::Txt( 'NOTA.TRIGGER_' . $this->triggers[ $sub ][ $nidt ] . '_EXPL' );
        $view = $this->getView( 'notifications' );
        $view->assign( $ids, 'settings' );
        $view->assign( $nid, 'nid' );
        $view->assign( $title, 'trigger_name' );
        $view->loadConfig( 'config.notification' );
        $view->setTemplate( 'config.notification' );
        $view->display();
    }

    private function getIds( $nid, &$sub, &$nidt, &$ids )
    {
        $sub = 'entry';
        $n = explode( '.', $nid );
        if ( count( $n ) > 2 ) {
            $sub = $n[ 0 ];
            array_shift( $n );
            $nidt = implode( '.', $n );

        }
        else {
            $nidt = $nid;
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
        $this->loadTriggers();
        SPFactory::registry()->loadDBSection( 'notifications' );
        $setting = Sobi::Reg( 'notifications.settings.params' );
        if ( strlen( $setting ) ) {
            $setting = SPConfig::unserialize( $setting );
        }
        if ( is_array( $setting ) && isset( $setting[ Sobi::Section() ][ $nid ] ) ) {
            $setting = $setting[ Sobi::Section() ][ $nid ];
        }
        elseif ( is_array( $setting ) && isset( $setting[ Sobi::Section() ][ $nidt ] ) ) {
            $setting = $setting[ Sobi::Section() ][ $nidt ];
        }
        elseif ( is_array( $setting ) && isset( $setting[ -1 ][ $nid ] ) ) {
            $setting = $setting[ -1 ][ $nid ];
        }
        else {
            $this->getDefault( $setting, $nidt );
        }

        foreach ( $ids as $id => $v ) {
            $value = SPLang::getValue( $nid . '.' . $id, 'application', Sobi::Section() );
            if ( !( strlen( $value ) ) ) {
                $value = SPLang::getValue( $nidt . '.' . $id, 'application', Sobi::Section() );
            }
            if ( !( strlen( $value ) ) ) {
                $value = SPLang::getValue( $nid . '.' . $id, 'application', -1 );
            }
            if ( strlen( $value ) ) {
                $ids[ $id ] = $value;
            }
            else {
                // otherwise get the defaults
                if ( isset( $setting[ $id ] ) ) {
                    $ids[ $id ] = $setting[ $id ];
                }
                else {
                    $ids[ $id ] = $v;
                }
            }
        }
    }

    private function getDefault( &$setting, $nid )
    {
        foreach ( $this->triggers as $subject => $values ) {
            if ( in_array( $nid, array_keys( $values ) ) ) {
                if ( isset( $this->parsers[ $subject ] ) ) {
                    if ( SPLoader::translatePath( $this->parsers[ $subject ] ) ) {
                        $handler = SPFactory::Instance( $this->parsers[ $subject ] );
                        if ( method_exists( $handler, 'defaultMessage' ) ) {
                            $handler->defaultMessage( $nid, $setting );
                        }
                    }
                }
                break;
            }
        }
    }

    private function loadTriggers()
    {
        $this->triggers = SPLoader::loadIniFile( 'etc.notifications' );
        $custom = SPLoader::translateDirPath( 'etc.napp_cfg' );
        $dummy = array();
        if ( $custom ) {
            $this->parsers = SPNotifications::custom( $custom, $this->triggers, $dummy, true );
        }
        Sobi::Trigger( 'Notification', 'LoadTriggers', array( &$this->triggers ) );
    }

    private function nList()
    {
        $this->loadTriggers();
        $view = $this->getView( 'notifications' );
        /* create the header */
        $view->assign(
            SPLists::tableHeader(
                array(
                    'name' => SP_TBL_HEAD_RAW,
                    'id' => SP_TBL_HEAD_RAW,
                    'username' => SP_TBL_HEAD_RAW,
                    'state' => SP_TBL_HEAD_RAW,
                    'state' => SP_TBL_HEAD_RAW,
                    'groups' => SP_TBL_HEAD_RAW,
                ),
                'notifications', 'c_sid', 'uorder'
            ),
            'header'
        );

        SPFactory::registry()->loadDBSection( 'notifications' );
        $setting = Sobi::Reg( 'notifications.settings.params' );
        if ( strlen( $setting ) ) {
            $setting = SPConfig::unserialize( $setting );
        }
        if ( is_array( $setting ) && isset( $setting[ Sobi::Section() ] ) ) {
            $setting = $setting[ Sobi::Section() ];
        }
        $triggers = array();
        if ( count( $this->triggers ) ) {
            foreach ( $this->triggers as $subject => $t ) {
                $subjectTitle = Sobi::Txt( 'NOTA.TRIGGER_GRP_' . strtoupper( $subject ) );
                if ( count( $t ) ) {
                    $sub = array();
                    foreach ( $t as $trigger => $label ) {
                        $expl = Sobi::Txt( 'NOTA.TRIGGER_' . $label . '_EXPL' );
                        $label = Sobi::Txt( 'NOTA.TRIGGER_' . $label );
                        $nid = $subject . '.' . $trigger;
                        $url = Sobi::Url( array( 'task' => 'notifications.edit', 'nid' => $subject . '.' . $trigger, 'sid' => Sobi::Section() ) );
                        $nstate = 0;
                        if ( isset( $setting[ $nid ] ) ) {
                            $nstate = $setting[ $nid ][ 'enabled' ];
                        }
                        elseif ( isset( $setting[ $trigger ] ) ) {
                            $nstate = $setting[ $trigger ][ 'enabled' ];
                        }
                        $sub[ ] = array(
                            'id' => $trigger,
                            'label' => "<a href=\"{$url}\">" . $label . '</a>',
                            'expl' => $expl,
                            'state' => $nstate
                        );
                    }
                    $triggers[ $subjectTitle ] = $sub;
                }
            }
        }
        $view->assign( $triggers, 'triggers' );
        $view->loadConfig( 'config.notifications' );
        $view->setTemplate( 'config.notifications' );
        $view->display();
    }
}
