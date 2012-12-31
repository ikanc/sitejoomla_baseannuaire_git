<?php
/**
 * @version: $Id: notifications.php 2094 2011-12-22 16:31:19Z Radek Suski $
 * @package: SobiPro Notifications Application
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
 * $Date: 2011-12-22 17:31:19 +0100 (Do, 22 Dez 2011) $
 * $Revision: 2094 $
 * $Author: Radek Suski $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );

class SPRRNotifications
{
    public function __construct()
    {
    }

    public function defaultMessage( $nid, &$setting )
    {
        static $triggers = array();
        if ( !( count( $triggers ) ) ) {
            $triggers = SPLoader::loadIniFile( 'etc.rra_msg' );
        }
        if ( isset( $triggers[ $nid ] ) ) {
            $setting = $triggers[ $nid ];
        }
        else {
            $setting = array(
                'from' => '{cfg:mail.fromname}',
                'fromMail' => '{cfg:mail.from}',
                'to' => '{user.name}',
                'toMail' => '{user.email}',
                'subject' => 'Message Subject',
                'body' => 'Message Body',
                'cc' => null,
                'bcc' => null,
                'html' => true,
                'enabled' => true
            );
        }
    }

    public function prepareMessageArgs( $action, &$settings, &$args )
    {
        if ( isset( $args[ 'review_data' ] ) ) {
            $rev = array(
                'title' => $args[ 'review_data' ]->get( 'title' ),
                'content' => $args[ 'review_data' ]->get( 'review' ),
                'date' => $args[ 'review_data' ]->get( 'date' ),
                'negatives' => $args[ 'review_data' ]->get( 'negativeReview' ),
                'positives' => $args[ 'review_data' ]->get( 'positiveReview' ),
                'author' => $args[ 'review_data' ]->get( 'author' ),
            );
            $rating = $args[ 'review_data' ]->get( 'rating' );
            $sid = 0;
            $ratings = array();
            if ( count( $rating ) ) {
                foreach ( $rating as $vote ) {
                    $sid = $vote[ 'sid' ];
                    $ratings[ $vote[ 'fid' ] ] = array(
                        'vote' => $vote[ 'vote' ],
                        'explanation' => $vote[ 'definition' ][ 'label' ],
                        'label' => $vote[ 'definition' ][ 'label' ]
                    );
                    $ratings[ SPLang::nid( $vote[ 'definition' ][ 'label' ] ) ] =& $ratings[ $vote[ 'fid' ] ];
                }
                $ratings[ 'average' ] = round( $args[ 'review_data' ]->get( 'oar' ), 2 );
            }
            switch ( $action ) {
                case 'AfterApprove.author':
                    $args[ 'user' ] = $args[ 'review_data' ]->get( 'author' );
                    break;
            }
            $args[ 'review' ] = $rev;
            $args[ 'rating' ] = $ratings;
            $args[ 'entry' ] = SPFactory::Entry( $sid );
            $args[ 'author' ] = SPFactory::Instance( 'cms.base.user', $args[ 'entry' ]->get( 'owner' ) );
            $args[ 'user' ] = SPFactory::user();
        }
    }
}

?>
