<?php
/**
 * @version: $Id: review.php 2093 2011-12-22 16:03:27Z Radek Suski $
 * @package: SobiPro Review & Rating Application
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
 * $Date: 2011-12-22 17:03:27 +0100 (Do, 22 Dez 2011) $
 * $Revision: 2093 $
 * $Author: Radek Suski $
 * $HeadURL: https://svn.suski.eu/SobiPro/Addons/trunk/Apps/ReviewRating/Site/ctrl/review.php $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadController( 'controller' );

/**
 * @author Radek Suski
 * @version 1.0
 * @created 15-Jul-2010 18:17:28
 */
class SPRr extends SPController
{
    /**
     * @var string
     */
    protected $_defTask = 'js';

    public function __construct()
    {
    }

    /**
     */
    public function execute()
    {
        $this->_task = $task = strlen( $this->_task ) ? $this->_task : $this->_defTask;
        if ( method_exists( $this, $this->_task ) ) {
            SPLang::load( 'SpApp.review_rating' );
            $this->$task();
        }
        else {
            Sobi::Error( 'SPRrCtrl', 'Task not found', SPC::WARNING, 404, __LINE__, __FILE__ );
        }
    }

    protected function report()
    {
        if ( !( SPFactory::mainframe()->checkToken() ) ) {
            Sobi::Error( 'Token', SPLang::e( 'UNAUTHORIZED_ACCESS_TASK', SPRequest::task() ), SPC::ERROR, 403, __LINE__, __FILE__ );
        }
        //$this->reportResponse( array( 'status' => 'failed', 'message' => Sobi::Txt( 'SPRRA.REPORT_MSG_FAILED' ) ) );
        $data = SPRequest::arr( 'reviewReport', array(), 'POST' );
        $messageData = array();
        $filter = SPFactory::registry()
                ->loadDBSection( 'fields_filter' )
                ->get( 'fields_filter.email.params' );
        if ( Sobi::My( 'id' ) ) {
            $messageData[ 'report' ][ 'author' ] = SPFactory::user();
        }
        elseif ( $data[ 'email' ] && preg_match( base64_decode( $filter ), trim( $data[ 'email' ] ) ) ) {
            $messageData[ 'report' ][ 'author' ] = array(
                'name' => $data[ 'author' ],
                'email' => trim( $data[ 'email' ] )
            );
            unset( $data[ 'author' ] );
            unset( $data[ 'email' ] );
        }
        else {
            $this->reportResponse( array( 'status' => 'error', 'message' => Sobi::Txt( 'SPRRA.ERR_MAIL_REQ' ) ) );
        }
        if ( $data[ 'subject' ] ) {
            $messageData[ 'report' ][ 'subject' ] = $data[ 'subject' ];
            unset( $data[ 'subject' ] );
        }
        else {
            $this->reportResponse( array( 'status' => 'error', 'message' => Sobi::Txt( 'SPRRA.ERR_REPORT_SUBJECT_REQ' ) ) );
        }
        if ( $data[ 'message' ] ) {
            $messageData[ 'report' ][ 'message' ] = $data[ 'message' ];
            unset( $data[ 'message' ] );
        }
        else {
            $this->reportResponse( array( 'status' => 'error', 'message' => Sobi::Txt( 'SPRRA.ERR_REPORT_MESSAGE_REQ' ) ) );
        }
        if ( count( $data ) ) {
            foreach ( $data as $i => $k ) {
                $messageData[ 'report' ][ $i ] = $k;
            }
        }
        $messageData[ 'review_data' ] = SPFactory::Instance( 'models.review', $data[ 'rid' ] );
        Sobi::Trigger( 'Review', 'Report', array( &$messageData ) );
        $send = SPFactory::db()
                ->select( 'send', 'spdb_notifications', array('mailDate' =>  'FUNCTION:NOW()',))
                ->loadResult();
        if( $send ) {
            $this->reportResponse( array( 'status' => 'ok', 'message' => Sobi::Txt( 'SPRRA.REPORT_MSG_THANK_YOU' ) ) );
        }
        else {
            $this->reportResponse( array( 'status' => 'failed', 'message' => Sobi::Txt( 'SPRRA.REPORT_MSG_FAILED' ) ) );
        }
    }

    private function reportResponse( $response )
    {
        SPFactory::mainframe()->cleanBuffer();
        echo json_encode( $response );
        exit;
    }

    protected function submit()
    {
        if ( !( Sobi::Can( 'review.add.own' ) ) || !( SPFactory::mainframe()->checkToken() ) ) {
            Sobi::Error( 'SPRrCtrl', 'UNAUTHORIZED_ACCESS', SPC::WARNING, 403, __LINE__, __FILE__ );
            exit;
        }
        $data = array();
        $model = SPFactory::Instance( 'models.review' );
        $data[ 'rating' ] = SPRequest::arr( 'sprating', array(), 'post' );
        $data[ 'review' ] = SPRequest::arr( 'spreview', array(), 'post' );
        $data[ 'review' ][ 'section' ] = Sobi::Section();

        try {
            $model->saveReview( $data );
            $this->axRsponse(
                array(
                    'response' => Sobi::Can( 'review.manage.own' ) || Sobi::Can( 'review.autopublish.own' ) ? SPReview::Txt( 'REV_STORED_PUBLISHED' ) : SPReview::Txt( 'REV_STORED_UNPUBLISHED' ),
                    'status' => 'ok'
                )
            );
        }
        catch ( SPException $x ) {
            $this->axRsponse( array( 'response' => $x->getMessage(), 'status' => 'failed' ) );
        }
    }

    private function axRsponse( $data )
    {
        if ( !( SPRequest::int( 'deb' ) ) ) {
            SPFactory::mainframe()->cleanBuffer();
            header( 'Content-type: text/javascript' );
        }
        echo json_encode( $data );
        exit;
    }
}

?>
