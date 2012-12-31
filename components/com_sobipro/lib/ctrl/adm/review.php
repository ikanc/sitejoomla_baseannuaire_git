<?php
/**
 * @version: $Id: review.php 2169 2012-01-19 09:59:18Z Radek Suski $
 * @package: SobiPro Review & Rating Application
 * ===================================================
 * @author
 * Name: Sigrid Suski & Radek Suski, Sigsiu.NET GmbH
 * Email: sobi[at]sigsiu.net
 * Url: http://www.Sigsiu.NET
 * ===================================================
 * @copyright Copyright (C) 2006 - 2011 Sigsiu.NET GmbH (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2012-01-19 10:59:18 +0100 (Do, 19 Jan 2012) $
 * $Revision: 2169 $
 * $Author: Radek Suski $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'config', true );

class SPRrCtrl extends SPConfigAdmCtrl
{
    /**
     * @var string
     */
    protected $_type = 'review';
    /**
     * @var string
     */
    protected $_defTask = 'config';

    public function execute()
    {
        $task = $this->_task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
        SPLang::load( 'SpApp.review_rating' );
        switch ( $this->_task ) {
            case 'config':
                {
                $this->checkInstall();
//				$helpTask = '';
//				SPFactory::registry()->set( 'task', $helpTask );
                $this->screen();
                Sobi::ReturnPoint();
                $r = true;
                break;
                }
            case 'list':
                {
                $this->listReviews();
                $r = true;
                Sobi::ReturnPoint();
                break;
                }
            case 'cancel':
                Sobi::Redirect( Sobi::Back(), null, null, true );
                break;
            case 'saveReview':
            case 'applyReview':
                $r = true;
                $this->saveReview( $this->_task == 'applyReview' );
                $r = true;
                break;
            default:
                {
                if ( method_exists( $this, $this->_task ) ) {
                    $this->$task();
                    $r = true;
                }
                else {
                    Sobi::Error( 'SPRrCtrl', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
                }
                break;
                }
        }
        return $r;
    }

    private function checkInstall()
    {
        $init = false;
        if ( !( SPLoader::path( 'usr.templates.' . Sobi::Cfg( 'section.template' ) . '.common.review', 'front', true, 'xsl' ) ) ) {
            if ( !( SPFs::copy( SPLoader::path( 'usr.templates.default.common.review', 'front', true, 'xsl' ), SPLoader::path( 'usr.templates.' . Sobi::Cfg( 'section.template' ) . '.common.review', 'front', false, 'xsl' ) ) ) ) {
                Sobi::Error( 'SPRrCtrl', 'Cannot copy template to the current template directory', SPC::WARNING, 0, __LINE__, __FILE__ );
            }
            else {
                $init = true;
            }
        }
        if ( $init || Sobi::Cfg( 'section.template' ) == 'default' ) {
            $fields = SPFactory::Model( 'review' )->reviewFields();
            if ( !( count( $fields ) ) ) {
                $def = array( 'Service', 'Communication', 'Support', 'Pricing' );
                foreach ( $def as $i => $value ) {
                    $a = array(
                        'enabled' => true,
                        'importance' => 5,
                        'sid' => Sobi::Section(),
                        'fid' => 0,
                        'section' => Sobi::Section(),
                        'position' => $i + 1,
                        'name' => $value,
                        'label' => $value,
                        'explanation' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Praesent nulla urna, molestie ut adipiscing vulputate, elementum eu nunc. Suspendisse eget sapien nec diam suscipit rutrum at id tellus.'
                    );
                    SPFactory::Model( 'review' )->storeField( $a );
                }
                $settings = array(
                    'ratingEnabled' => true,
                    'ratingRevReq' => true,
                    'revEnabled' => true,
                    'revMulti' => false,
                    'revMailRequ' => true,
                    'revOrdering' => 'date.asc',
                    'revOnSite' => 5,
                    'revPositive' => true,
                    'website' => false,
                );
                $this->save( $settings );
            }
        }
    }

    private function rdelete()
    {
        $rids = SPRequest::arr( 'rid', array() );
        if ( !count( $rids ) ) {
            Sobi::Redirect( Sobi::Back(), Sobi::Txt( 'SPRRA.ERR_NO_REV_TO_DEL' ), SPC::ERROR_MSG );
        }
        foreach ( $rids as $rid ) {
            SPFactory::Instance( 'models.review', $rid )->delete();
        }
        Sobi::Redirect( Sobi::Back(), Sobi::Txt( 'SPRRA.MSG_REVS_DELETED' ) );
    }

    private function saveReview( $apply = false )
    {
        if ( !( SPFactory::mainframe()->checkToken() ) ) {
            Sobi::Error( 'SPRrCtrl', 'UNAUTHORIZED_ACCESS', SPC::WARNING, 403, __LINE__, __FILE__ );
            exit;
        }
        $rid = SPRequest::int( 'rid' );
        $rev = SPFactory::Instance( 'models.review', $rid );
        $review = array(
            'review' => array(
                'title' => SPRequest::string( 'review_title' ),
                'review' => SPRequest::string( 'review_text' ),
                'neg_review' => SPRequest::string( 'review_negative' ),
                'pos_review' => SPRequest::string( 'review_positive' ),
                'rid' => $rid,
                'sid' => $rev->get( 'sid' ),
                'section' => Sobi::Section()
            ),
            'rating' => SPRequest::arr( 'rating', array(), 'post' )
        );
        if ( SPRequest::string( 'review_user_name' ) ) {
            $review[ 'review' ][ 'visitor' ] = SPRequest::string( 'review_user_name' );
            $review[ 'review' ][ 'vmail' ] = SPRequest::string( 'review_user_email' );
        }
        SPFactory::Instance( 'models.review', $rid )->saveReview( $review );
        if ( $apply ) {
            $msg = Sobi::Txt( 'MSG.OBJ_SAVED', array( 'type' => Sobi::Txt( 'SPRRA.REVIEW_OBJ' ) ) );
            Sobi::Redirect( Sobi::Url( array( 'task' => 'review.redit', 'rid' => $rid, 'pid' => Sobi::Section() ) ), $msg );
        }
        else {
            Sobi::Redirect( Sobi::Back(), Sobi::Txt( 'MSG.OBJ_SAVED', array( 'type' => Sobi::Txt( 'SPRRA.REVIEW_OBJ' ) ) ) );
        }
    }

    private function redit()
    {
        $rid = SPRequest::int( 'rid' );
        $rev = SPFactory::Instance( 'models.review', $rid );
        $rev->redefine();
        //		$entry = SPFactory::entry( $rev->get( 'sid' ) );
        //		$ehref = Sobi::Url( array( 'task' => 'entry.edit', 'sid' => $entry->get( 'id' ), 'pid' => Sobi::Section() ) );
        $view =& SPFactory::View( 'view', true );
        $view->loadConfig( 'extensions.sprredit' );
        $view->assign( $rev, 'review' );
        $view->addHidden( $rid, 'rid' );
        $view->addHidden( Sobi::Section(), 'sid' );
        $view->setTemplate( 'extensions.sprredit' );
        $view->display();
    }


    private function approve()
    {
        $rids = SPRequest::arr( 'rid', array() );
        if ( !count( $rids ) ) {
            $rids = array( SPRequest::int( 'rid' ) );
        }
        foreach ( $rids as $rid ) {
            SPFactory::Instance( 'models.review', $rid )->approve();
        }
        Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'SPRRA.MSG_REV_APPR' ) );
    }

    private function unapprove()
    {
        $rids = SPRequest::arr( 'rid', array() );
        if ( !count( $rids ) ) {
            $rids = array( SPRequest::int( 'rid' ) );
        }
        foreach ( $rids as $rid ) {
            SPFactory::Instance( 'models.review', $rid )->unapprove();
        }
        Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'SPRRA.MSG_REV_UNAPPR' ) );
    }

    private function publish()
    {
        $rids = SPRequest::arr( 'rid', array() );
        if ( !count( $rids ) && SPRequest::int( 'rid' ) ) {
            $rids = array( SPRequest::int( 'rid' ) );
        }
        else {
            SPFactory::db()->update( 'spdb_sprr_fields', array( 'enabled' => 1 ), array( 'fid' => SPRequest::int( 'fid' ) ) );
            SPFactory::db()->update( 'spdb_sprr_review', array( 'oar' => 0 ), array( 'section' => Sobi::Section() ) );
            Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' ) );
            return true;
        }
        foreach ( $rids as $rid ) {
            SPFactory::Instance( 'models.review', $rid )->publish();
        }
        Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'SPRRA.MSG_REV_PUBLISHED' ) );
    }

    private function hide()
    {
        $rids = SPRequest::arr( 'rid', array() );
        if ( !count( $rids ) && SPRequest::int( 'rid' ) ) {
            $rids = array( SPRequest::int( 'rid' ) );
        }
        else {
            SPFactory::db()->update( 'spdb_sprr_fields', array( 'enabled' => 0 ), array( 'fid' => SPRequest::int( 'fid' ) ) );
            SPFactory::db()->update( 'spdb_sprr_review', array( 'oar' => 0 ), array( 'section' => Sobi::Section() ) );
            Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' ) );
            return true;
        }
        foreach ( $rids as $rid ) {
            SPFactory::Instance( 'models.review', $rid )->unpublish();
        }
        Sobi::Redirect( Sobi::GetUserState( 'back_url', Sobi::Url() ), Sobi::Txt( 'SPRRA.MSG_REV_UNPUBLISHED' ) );
    }

    private function listReviews()
    {
        $eLimit = Sobi::GetUserState( 'adm.entries.limit', 'elimit', Sobi::Cfg( 'adm_list.entries_limit', 15 ) );
        $eLimStart = SPRequest::int( 'eLimStart', 0 );
        $Limit = $eLimit > 0 ? $eLimit : 0;
        $LimStart = $eLimStart ? ( ( $eLimStart - 1 ) * $eLimit ) : $eLimStart;
        $pageNav = SPLoader::loadClass( 'helpers.adm.pagenav' );
        SPLoader::loadClass( 'helpers.adm.lists' );

        $revsCount = SPFactory::db()
                ->select( 'COUNT(*)', 'spdb_sprr_review', array( 'section' => Sobi::Section() ), 'rDate.desc' )
                ->loadResult();
        $revs = SPFactory::db()
                ->select( '*', 'spdb_sprr_review', array( 'section' => Sobi::Section() ), 'rDate.desc', $Limit, $LimStart )
                ->loadAssocList();
        $reviews = array();
        if ( count( $revs ) ) {
            foreach ( $revs as $id => $rev ) {
                $sentry = SPFactory::entry( $rev[ 'sid' ] );
                if ( $rev[ 'uid' ] ) {
                    $user = SPUser::getBaseData( array( $rev[ 'uid' ] ) );
                    $user = $user[ $rev[ 'uid' ] ];
                    $uUrl = SPUser::userUrl( $rev[ 'uid' ] );
                    $author = "<a href=\"{$uUrl}\">{$user->name}</a>";

                }
                else {
                    $author = "<a href=\"mailto:{$rev[ 'uEmail' ]}\">{$rev[ 'uName' ]}</a>" . ' ' . Sobi::Txt( 'SPRRA.UNREG_VISITOR' );
                }
                $revs[ $id ][ 'entry' ] = $sentry;
                $rev[ 'id' ] = $rev[ 'rid' ];
                $rev[ 'oType' ] = 'review';
                $row = new SPObject();
                $row->castArray( $rev );
                $ehref = Sobi::Url( array( 'task' => 'entry.edit', 'sid' => $sentry->get( 'id' ), 'pid' => Sobi::Section() ) );
                $rhref = Sobi::Url( array( 'task' => 'review.redit', 'rid' => $rev[ 'rid' ], 'pid' => Sobi::Section() ) );
                $reviews[ ] = array(
                    'id' => $rev[ 'rid' ],
                    'checkbox' => SPLists::checkedOut( $row, 'rid' ),
                    'entry_name' => '<a href="' . $ehref . '">' . ( strlen( $sentry->get( 'name' ) ) ? $sentry->get( 'name' ) : Sobi::Txt( 'No Name' ) ) . '</a>',
                    'title' => '<a href="' . $rhref . '" title="' . $row->get( 'rTitle' ) . '">' . $row->get( 'rTitle' ) . '</a>',
                    'author' => $author,
                    'state' => SPLists::state( $row, 'rid', 'review', 'state', null, Sobi::Section() ),
                    'approval' => str_replace( 'sid=', 'pid=' . Sobi::Section() . '&amp;rid=', SPLists::approval( $row ) ),
                    'date' => $rev[ 'rDate' ],
                    'oar' => $rev[ 'oar' ]
                );
            }
        }
        $view = $this->getView( 'sprr' );
        $fields = array(
            'checkbox' => SP_TBL_HEAD_SELECTION_BOX,
            'rid' => SP_TBL_HEAD_RAW,
            'name' => SP_TBL_HEAD_RAW,
            'entry' => SP_TBL_HEAD_RAW,
            'oar' => SP_TBL_HEAD_RAW,
            'title' => SP_TBL_HEAD_RAW,
            'state' => SP_TBL_HEAD_RAW,
            'approved' => SP_TBL_HEAD_RAW,
            'owner' => SP_TBL_HEAD_RAW,
            'date' => SP_TBL_HEAD_RAW,
        );
        $pageNav = new $pageNav( $eLimit, $revsCount, $eLimStart, 'SPEntriesPageNav', 'elimit', 'SPEntriesPageLimit' );
        $view->assign( $pageNav->display( true ), 'page_nav' );
        $view->assign( SPLists::tableHeader( $fields, 'review', 'rid', 'rorder' ), 'header' );
        $view->loadConfig( 'extensions.sprrlist' );
        $view->setTemplate( 'extensions.sprrlist' );
        $view->assign( $reviews, 'reviews' );
        $view->addHidden( $eLimStart, 'eLimStart' );
        $menu =& $view->get( 'menu' );
        $menu->setOpen( 'AMN.ENT_CAT' );
        $view->assign( $menu, 'menu' );
        $view->display();
    }

    private function deleteField()
    {
        $fid = SPRequest::int( 'fid' );
        SPFactory::db()->delete( 'spdb_sprr_rating', array( 'fid' => $fid ) );
        SPFactory::db()->delete( 'spdb_sprr_fields', array( 'fid' => $fid ) );
        SPFactory::db()->delete( 'spdb_language', array( 'fid' => $fid, 'sKey' => 'sprr_field', 'oType' => 'sprr_field', 'id' => $fid ) );
        echo '<script>parent.location.reload();</script>';
    }

    private function saveField()
    {
        if ( !( SPFactory::mainframe()->checkToken() ) ) {
            Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
        }
        $request = array();
        $request[ 'fid' ] = SPRequest::int( 'fid' );
        $request[ 'enabled' ] = SPRequest::int( 'field_enabled' );
        $request[ 'importance' ] = SPRequest::int( 'field_importance' );
        $request[ 'sid' ] = Sobi::Section();
        $request[ 'position' ] = SPRequest::int( 'field_position' );
        $request[ 'key' ] = $request[ 'type' ] = 'sprr_field';
        $request[ 'value' ] = SPRequest::string( 'field_label' );
        $request[ 'section' ] = Sobi::Section();
        $request[ 'id' ] = $request[ 'fid' ];
        $request[ 'explanation' ] = str_replace( array( "\n", "\t", "\r" ), null, SPLang::clean( SPRequest::string( 'field_explanation' ) ) );
        $request[ 'name' ] = SPRequest::string( 'field_label' );
        $request[ 'section' ] = Sobi::Section();
        SPFactory::Model( 'review' )->storeField( $request );
        SPMainFrame::msg( array( 'msg' => Sobi::Txt( 'SPRRA.SETTINGS_FIELD_SAVED' ) ) );
        echo '<script>parent.location.reload();</script>';
    }

    private function editField()
    {
        $id = SPRequest::int( 'fid' );
        $fields = $this->reviewFields();
        if ( isset( $fields[ $id ] ) ) {
            $field = $fields[ $id ];
        }
        else {
            $field = array( 'fid' => 0, 'sid' => 0, 'enabled' => true, 'label' => '', 'explanation' => '', 'id' => 0, 'importance' => 5, 'position' => count( $fields ) + 1 );
        }
        $raw = Sobi::Url( array( 'out' => 'raw' ), true );
        $raw = explode( '&', $raw );
        $view =& SPFactory::View( 'view', true );
        $view->assign( $this->_task, 'task' );
        $view->loadConfig( 'extensions.sprr' );
        $view->assign( $field, 'field' );
        if ( count( $raw ) ) {
            foreach ( $raw as $line ) {
                if ( !( strstr( $line, '?' ) ) ) {
                    $line = explode( '=', $line );
                    $view->addHidden( $line[ 1 ], $line[ 0 ] );
                }
            }
        }
        $view->addHidden( $id, 'fid' );
        $view->addHidden( Sobi::Section(), 'sid' );
        $view->setTemplate( 'extensions.sprrfield' );
        $view->display();
    }

    private function screen()
    {
        SPLoader::loadClass( 'html.tooltip' );
        $registry = SPFactory::registry();
        $registry->loadDBSection( 'sprr_' . Sobi::Section() );
        $set = $registry->get( 'sprr_' . Sobi::Section() );
        $settings = array();
        if ( !( count( $set ) ) ) {
            $settings = array(
                'ratingEnabled' => true,
                'ratingRevReq' => true,
                'revEnabled' => true,
                'revMulti' => false,
                'revMailRequ' => true,
                'revOrdering' => 'date.asc',
                'revOnSite' => 5,
                'revPositive' => true,
                'website' => false,
            );
        }
        else {
            foreach ( $set as $k => $v ) {
                $settings[ $k ] = $v[ 'value' ];
            }
        }
        $subjects = SPLang::getValue( 'sprr_report_msg', 'application', Sobi::Section() );
        if ( strlen( $subjects ) < 10 ) {
            SPLang::load( 'SpApp.review_rating' );
            $subjects = SPLang::clean( str_replace( "'", '"', Sobi::Txt( 'SPRRA.SETTINGS_REPORTS_DEF_SUBJECTS' ) ) );
        }
        $settings[ 'revReportTypes' ] = implode( "\n", json_decode( $subjects ) );
        $view = $this->getView( 'sprr' );
        $view->loadConfig( 'extensions.sprr' );
        $view->setTemplate( 'extensions.sprr' );
        $view->assign( $this->reviewFields(), 'fields' );
        $view->assign( $settings, 'settings' );
        $view->assign( Sobi::Url( array( 'task' => 'review.editField', 'out' => 'html', 'sid' => Sobi::Section() ), true ), 'edit_url' );
        $menu =& $view->get( 'menu' );
        $menu->setOpen( 'AMN.SEC_CFG' );
        $view->assign( $menu, 'menu' );
        $view->display();
    }

    private function reviewFields()
    {
        return SPFactory::Model( 'review' )->reviewFields();
    }

    protected function save( $settings = null )
    {
        $r = false;
        if ( !( $settings ) ) {
            if ( !( SPFactory::mainframe()->checkToken() ) ) {
                Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
            }
            $settings = SPRequest::search( 'settings' );
            unset( $settings[ 'settings_revReportTypes' ] );
            $r = true;
        }
        $reportTypes = SPRequest::string( 'settings_revReportTypes' );
        $reportTypes = explode( "\n", $reportTypes );
        if ( count( $reportTypes ) ) {
            $data = array();
            $data[ 'id' ] = 1;
            $data[ 'value' ] = json_encode( $reportTypes );
            $data[ 'key' ] = 'sprr_report_msg';
            $data[ 'type' ] = 'application';
            $data[ 'section' ] = Sobi::Section();
            SPLang::saveValues( $data );
        }
        $store = array();
        foreach ( $settings as $k => $value ) {
            $key = str_replace( 'settings_', null, $k );
            $store[ $key ] = array( 'key' => $key, 'value' => $value );
        }
        SPFactory::registry()->saveDBSection( $store, 'sprr_' . Sobi::Section() );
        if ( $r ) {
            Sobi::Redirect( SPMainFrame::getBack(), Sobi::Txt( 'MSG.ALL_CHANGES_SAVED' ) );
        }
    }
}
