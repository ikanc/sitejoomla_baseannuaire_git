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
 * @copyright Copyright (C) 2010 Sigsiu.NET (http://www.sigsiu.net). All rights reserved.
 * @license see http://www.gnu.org/licenses/gpl.html GNU/GPL Version 3.
 * You can use, redistribute this file and/or modify it under the terms of the GNU General Public License version 3
 * ===================================================
 * $Date: 2012-01-19 10:59:18 +0100 (Do, 19 Jan 2012) $
 * $Revision: 2169 $
 * $Author: Radek Suski $
 */
defined( 'SOBIPRO' ) || exit( 'Restricted access' );

/**
 * @author Radek Suski
 * @version 1.0
 */
class SPReview extends SPObject
{
    /**
     * @var int
     */
    protected $rid = 0;
    /**
     * @var array
     */
    protected $data = null;
    /**
     * @var string
     */
    protected $title = null;
    /**
     * @var string
     */
    protected $review = null;
    /**
     * @var string
     */
    protected $negativeReview = null;
    /**
     * @var string
     */
    protected $positiveReview = null;
    /**
     * @var string
     */
    protected $date = null;
    /**
     * @var array
     */
    protected $author = array();
    /**
     * @var bool
     */
    protected $approved = false;
    /**
     * @var bool
     */
    protected $state = false;
    /**
     * @var array
     */
    protected $rating = array();
    /**
     * @var string
     */
    protected $approvedAt = null;
    /**
     * @var string
     */
    protected $approvedIp = null;
    /**
     * @var string
     */
    protected $editedAt = null;
    /**
     * @var string
     */
    protected $editedBy = null;
    /**
     * @var string
     */
    protected $editedIp = null;
    /**
     * @var float
     */
    protected $oar = 0;
    /**
     * @var array
     */
    private $rel = array( 'section' => 'section', 'rid' => 'rid', 'sid' => 'sid', 'uid' => 'uid', 'title' => 'rTitle', 'review' => 'rReview', 'neg_review' => 'rNeg', 'pos_review' => 'rPos', 'date' => 'rDate', 'visitor' => 'uName', 'vmail' => 'uEmail', 'oar' => 'oar', 'rating' => 'rRating' );
    /**
     * @var int
     */
    protected $sid = 0;
    /**
     * @var int
     */
    protected $ratingEnabled = 1;
    /**
     * @var int
     */
    protected $ratingRevReq = 1;

    /**
     * @param int $id
     */
    public function __construct( $id = 0 )
    {
        $this->rid = $id;
        if ( $id ) {
            $this->load( $this->rid );
        }
        static $settings = null;
        if ( !( $settings ) ) {
            $registry = SPFactory::registry();
            $registry->loadDBSection( 'sprr_' . Sobi::Section() );
            $settings = $registry->get( 'sprr_' . Sobi::Section() );
        }
        if ( count( $settings ) ) {
            foreach ( $settings as $setting => $value ) {
                $this->$setting = $value[ 'value' ];
            }
            $this->revOrdering = 'r' . $this->revOrdering;
        }
    }

    protected function & load( $id )
    {
        $rev = SPFactory::db()->select( '*', 'spdb_sprr_review', array( 'rid' => $id ) )->loadObject();
        if ( $rev ) {
            if ( $rev->uid ) {
                $user = SPUser::getBaseData( array( $rev->uid ) );
                $user = $user[ $rev->uid ];
                $this->author = array( 'uid' => $rev->uid, 'name' => $user->name, 'email' => $user->email, 'editable' => false );
            }
            else {
                $this->author = array( 'uid' => $rev->uid, 'name' => $rev->uName, 'email' => $rev->uEmail, 'editable' => true );
            }
            $this->title = $rev->rTitle;
            $this->review = $rev->rReview;
            $this->negativeReview = $rev->rNeg;
            $this->positiveReview = $rev->rPos;
            $this->date = $rev->rDate;
            $this->approved = $rev->approved;
            $this->state = $rev->state;
            $this->rating = unserialize( $rev->rRating );
            $this->approvedAt = $rev->appAt;
            $this->approvedIp = $rev->appIP;
            $this->editedAt = $rev->editedAt;
            $this->editedBy = $rev->editedBy;
            $this->editedIp = $rev->editedIP;
            $this->oar = $rev->oar;
            if ( !( $this->oar ) ) {
                $this->recount( false );
            }
            elseif ( $this->oar < 0 ) {
                $this->recount();
            }
            $this->sid = $rev->sid;
            $fields = $this->reviewFields();
            foreach ( $this->rating as $fid => $rating ) {
                if ( isset( $fields[ $fid ] ) ) {
                    $this->rating[ $fid ][ 'definition' ] = $fields[ $fid ];
                }
                else {
                    unset( $this->rating[ $fid ] );
                    $this->recount( false );
                }
            }
        }
        return $this;
    }

    public function & redefine()
    {
        $fields = $this->reviewFields();
        foreach ( $fields as $fid => $field ) {
            if ( !( isset( $this->rating[ $fid ] ) ) ) {
                $this->rating[ $fid ] = array(
                    'sid' => $this->sid,
                    'fid' => $fid,
                    'vote' => 0,
                    'oai' => 0,
                    'definition' => $field
                );
            }
        }
        return $this;
    }

    public function & setSid( $sid )
    {
        $this->sid = $sid;
        return $this;
    }

    /**
     *
     */
    public function setDetails( &$entry, $site = 1 )
    {
        if ( Sobi::Can( 'review.add.own' ) ) {
            $enabled = $this->revEnabled;
            if ( !( $this->revMulti ) && $enabled ) {
                $enabled = $this->userReviews( true ) ? false : true;
            }
            if ( $enabled ) {
                $entry[ 'review_form' ][ 'settings' ] = array(
                    'email_required' => Sobi::My( 'id' ) ? false : $this->revMailRequ,
                    'review_enabled' => $enabled && $this->revEnabled,
                    'rating_enabled' => $enabled && $this->ratingEnabled,
                    'rating_required' => $this->ratingRevReq,
                    'positive_negative' => $this->revPositive,
                    'name_required' => Sobi::My( 'id' ) ? false : true,
                    'token' => SPFactory::mainframe()->token()
                );
                $entry[ 'review_form' ][ 'fields' ] = $this->reviewFieldsView();
            }
            else {
                $entry[ 'review_form' ][ 'settings' ] = array(
                    'review_enabled' => false,
                    'rating_enabled' => false,
                    'positive_negative' => $this->revPositive,
                );
            }
        }
        if ( Sobi::Can( 'review.see.valid' ) ) {
            $db =& SPFactory::db();
            $perms = array( 'sid' => $this->sid );
            if ( !( Sobi::Can( 'review.see.any' ) ) ) {
                if ( !( Sobi::Can( 'review.see.own_unpublished' ) ) ) {
                    $perms[ 'state' ] = 1;
                }
                else {
                    $perms = $db->argsOr( array( 'state' => '1', 'uid' => Sobi::My( 'id' ) ) );
                }
            }
            $count = $db->select( 'count(rid)', 'spdb_sprr_review', $perms, $this->revOrdering )->loadResult();
            $reviews = array();
            if ( $count ) {
                $eLimStart = ( ( $site - 1 ) * $this->revOnSite );
                $revs = $db->select( 'rid', 'spdb_sprr_review', $perms, $this->revOrdering, $this->revOnSite, $eLimStart )->loadResultArray();
                if ( $revs && count( $revs ) ) {
                    foreach ( $revs as $rev ) {
                        $reviews[ ] = new self( $rev );
                    }
                }
            }
            if ( count( $reviews ) ) {
                foreach ( $reviews as $review ) {
                    $author = $review->get( 'author' );
                    $ratings = $review->get( 'rating' );
                    $r = array();
                    foreach ( $ratings as $rating ) {
                        $r[ ] = array(
                            '_complex' => 1,
                            '_data' => $rating[ 'vote' ],
                            '_attributes' => array( 'id' => $rating[ 'fid' ], 'label' => $rating[ 'definition' ][ 'label' ], 'importance' => $rating[ 'definition' ][ 'importance' ] )
                        );
                    }
                    $input[ 'text' ] = array(
                        '_complex' => 1,
                        '_xml' => true,
                        '_data' => nl2br( $review->get( 'review' ) )
                    );
                    if ( strlen( $review->get( 'positiveReview' ) ) ) {
                        $input[ 'positives' ] = explode( ',', $review->get( 'positiveReview' ) );
                    }
                    if ( strlen( $review->get( 'negativeReview' ) ) ) {
                        $input[ 'negatives' ] = explode( ',', $review->get( 'negativeReview' ) );
                    }
                    $entry[ 'reviews' ][ ] = array(
                        '_complex' => 1,
                        '_data' => array(
                            'title' => $review->get( 'title' ),
                            'input' => $input,
                            'author' => array(
                                '_complex' => 1,
                                '_data' => $author[ 'name' ],
                                '_attributes' => array( 'id' => $author[ 'uid' ] )
                            ),
                            'ratings' => $r
                        ),
                        '_attributes' => array(
                            'id' => $review->get( 'rid' ),
                            'date' => $review->get( 'date' ),
                            'oar' => $review->get( 'oar' ),
                            'published' => $review->get( 'state' ),
                        ),
                    );
                }
                if ( $this->revReportsEnabled && ( Sobi::My( 'id' ) || $this->revReportsAnonymous ) ) {
                    $subjects = SPLang::getValue( 'sprr_report_msg', 'application', Sobi::Section() );
                    if ( !( strlen( $subjects ) ) ) {
                        $subjects = Sobi::Txt( 'SPRRA.SETTINGS_REPORTS_DEF_SUBJECTS' );
                    }
                    $subjects = json_decode( $subjects );
                    if ( count( $subjects ) ) {
                        foreach ( $subjects as $i => $s ) {
                            $subjects[ $i ] = trim( $s );
                        }
                    }
                    $txt = array(
                        'report' => Sobi::Txt( 'SPRRA.REPORT_LABEL' ),
                        'select_subject' => Sobi::Txt( 'SPRRA.REPORT_SELECT_SUBJECT_LABEL' ),
                        'enter_subject' => Sobi::Txt( 'SPRRA.REPORT_ENTER_SUBJECT_LABEL' ),
                        'enter_text' => Sobi::Txt( 'SPRRA.REPORT_ENTER_TEXT_LABEL' ),
                        'send_bt' => Sobi::Txt( 'SPRRA.REPORT_SEND_BUTTON' ),
                        'window_title' => Sobi::Txt( 'SPRRA.REPORT_WINDOW_HEAD' ),
                    );
                    if ( !( Sobi::My( 'id' ) ) ) {
                        $txt[ 'enter_name' ] = Sobi::Txt( 'SPRRA.REPORT_ENTER_NAME_LABEL' );
                        $txt[ 'enter_email' ] = Sobi::Txt( 'SPRRA.REPORT_ENTER_EMAIL_LABEL' );
                    }
                    foreach ( $txt as $i => $s ) {
                        $labels[ ] = array(
                            '_complex' => 1,
                            '_data' => trim( $s ),
                            '_attributes' => array( 'label' => $i )
                        );
                    }
                    $entry[ 'reviews' ][ 'report_form' ] = array(
                        'texts' => $labels,
                        'subjects' => $subjects
                    );
                }
                $rs = $this->countAverage();
                $entry[ 'reviews' ][ 'summary_rating' ][ 'overall' ] = array( '_complex' => 1, '_data' => round( $rs[ 'oar' ], 2 ), '_attributes' => array( 'count' => $rs[ 'count' ], 'value' => $rs[ 'oar' ] ) );
                if ( isset( $rs[ 'detailed' ] ) && count( $rs[ 'detailed' ] ) ) {
                    $r = array();
                    foreach ( $ratings as $rating ) {
                        if ( isset( $rs[ 'detailed' ][ $rating[ 'fid' ] ] ) ) {
                            $r[ ] = array(
                                '_complex' => 1,
                                '_data' => round( $rs[ 'detailed' ][ $rating[ 'fid' ] ][ 'ar' ], 2 ),
                                '_attributes' => array( 'id' => $rating[ 'fid' ], 'label' => $rating[ 'definition' ][ 'label' ], 'count' => $rs[ 'detailed' ][ $rating[ 'fid' ] ][ 'count' ], 'value' => $rs[ 'detailed' ][ $rating[ 'fid' ] ][ 'ar' ] )
                            );
                        }
                    }
                    $entry[ 'reviews' ][ 'summary_rating' ][ 'fields' ] = $r;
                }
                /* create page navigation */
                $pnc = SPLoader::loadClass( 'helpers.pagenav_xslt' );
                $pn = new $pnc( $this->revOnSite, $count, $site, array( 'sid' => $this->sid, 'title' => $entry[ 'entry' ][ '_data' ][ 'name' ][ '_data' ] ) );
                $entry[ 'reviews' ][ 'navigation' ] = array(
                    '_complex' => 1,
                    '_data' => $pn->get(),
                    '_attributes' => array( 'lang' => Sobi::Lang( false ) )
                );
            }
        }
    }

    public function countAverage( $sid = 0 )
    {
        $sid = $sid ? $sid : $this->sid;
        $r = SPFactory::db()->select( array( 'AVG(oar)', 'COUNT(*)' ), 'spdb_sprr_review', array( 'sid' => $sid, 'state' => 1 ) )
                ->loadAssocList();
        $rating = array(
            'oar' => $r[ 0 ][ 'AVG(oar)' ],
            'count' => $r[ 0 ][ 'COUNT(*)' ]
        );
        $r = SPFactory::db()->select( array( 'AVG(vote)', 'COUNT(*)', 'fid' ), 'spdb_sprr_rating', array( 'sid' => $sid, 'state' => 1 ), null, 0, 0, true, 'fid' )
                ->loadAssocList( 'fid' );
        $rd = array();
        if ( count( $r ) ) {
            foreach ( $r as $v ) {
                $rd[ 'ar' ] = $v[ 'AVG(vote)' ];
                $rd[ 'count' ] = $v[ 'COUNT(*)' ];
                $rating[ 'detailed' ][ $v[ 'fid' ] ] = $rd;
            }
        }
        return $rating;
    }

    /**
     *
     */
    private function userReviews( $count = false, $data = array() )
    {
        $result = null;
        if ( $count ) {
            if ( Sobi::My( 'id' ) ) {
                $result = SPFactory::db()->select(
                    'COUNT(*)',
                    'spdb_sprr_review',
                    array( 'uid' => Sobi::My( 'id' ), 'sid' => $this->sid )
                )->loadResult();
            }
            elseif ( count( $data ) && isset( $data[ 'review' ][ 'vmail' ] ) ) {
                $result = SPFactory::db()->select(
                    'COUNT(*)',
                    'spdb_sprr_review',
                    array( 'uEmail' => $data[ 'review' ][ 'vmail' ], 'sid' => $this->sid )
                )->loadResult();
            }
            else {
                /* @todo: limit the time to not prevent forever */
                $result = SPFactory::db()->select(
                    'COUNT(*)',
                    'spdb_sprr_review',
                    array( 'uIP' => SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' ), 'sid' => $this->sid )
                )->loadResult();
            }
        }
        else {

        }
        return $result;
    }

    /**
     *
     */
    public function delete()
    {
        Sobi::Trigger( 'Review', 'BeforeDelete', array( array( 'review' => &$this ) ) );
        SPFactory::db()->delete( 'spdb_sprr_rating', array( 'rid' => $this->rid ) );
        SPFactory::db()->delete( 'spdb_sprr_review', array( 'rid' => $this->rid ) );
    }

    /**
     *
     */
    public function saveReview( $data )
    {
        $this->validateInput( $data, isset( $data[ 'review' ][ 'rid' ] ) );
        $action = 'save';
        /* @todo: */
        if ( !( $this->revMulti ) && ( !( Sobi::My( 'id' ) ) ) ) {

        }
        $this->countRating( $data );
        if ( isset( $data[ 'review' ][ 'sid' ] ) ) {
            $this->sid = $data[ 'review' ][ 'sid' ];
        }
        if ( !( isset( $data[ 'review' ][ 'rid' ] ) ) ) {
            try {
                SPFactory::db()->insert(
                    'spdb_sprr_review',
                    array(
                        'rid' => 0,
                        'sid' => $data[ 'review' ][ 'sid' ],
                        'section' => isset( $data[ 'review' ][ 'section' ] ) ? $data[ 'review' ][ 'section' ] : Sobi::Section(),
                        'rTitle' => $data[ 'review' ][ 'title' ],
                        'rReview' => $data[ 'review' ][ 'review' ],
                        'rNeg' => $data[ 'review' ][ 'neg_review' ],
                        'rPos' => $data[ 'review' ][ 'pos_review' ],
                        'rDate' => SPRequest::now(),
                        'uid' => Sobi::My( 'id' ),
                        'uName' => $data[ 'review' ][ 'visitor' ],
                        'uEmail' => $data[ 'review' ][ 'vmail' ],
                        'uIP' => SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' ),
                        'approved' => Sobi::Can( 'review.manage.own' ) || Sobi::Can( 'review.autopublish.own' ) ? Sobi::My( 'id' ) : 0,
                        'state' => Sobi::Can( 'review.manage.own' ) || Sobi::Can( 'review.autopublish.own' ),
                        'rRating' => serialize( $data[ 'rating' ][ 'fields' ] ),
                        'rParams' => null,
                        'appAt' => Sobi::Can( 'review.manage.own' ) || Sobi::Can( 'review.autopublish.own' ) ? SPRequest::now() : null,
                        'appIP' => Sobi::Can( 'review.manage.own' ) || Sobi::Can( 'review.autopublish.own' ) ? SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' ) : null,
                        'editedAt' => null,
                        'editedBy' => 0,
                        'editedIP' => null,
                        'oar' => $data[ 'rating' ][ 'oar' ],
                        'hc' => 0
                    )
                );
                $rid = SPFactory::db()->insertid();
            }
            catch ( SPException $x ) {
                Sobi::Error( __CLASS__, SPLang::e( 'Cannot save review. Msg %s', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
                throw new SPException( SPReview::Txt( 'ERR_CANNOT_STORE' ) );
            }
        }
        else {
            $action = 'update';
            $rid = $data[ 'review' ][ 'rid' ];
            $rev = array();
            foreach ( $data[ 'review' ] as $k => $v ) {
                $rev[ $this->rel[ $k ] ] = $v;
            }
            $rev[ 'editedAt' ] = SPRequest::now();
            $rev[ 'editedBy' ] = Sobi::My( 'id' );
            $rev[ 'editedIP' ] = SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' );
            $rev[ 'sid' ] = $this->sid;
            $rev[ 'oar' ] = $data[ 'rating' ][ 'oar' ];
            if ( isset( $data[ 'rating' ][ 'fields' ] ) && count( $data[ 'rating' ][ 'fields' ] ) ) {
                $rev[ 'rRating' ] = serialize( $data[ 'rating' ][ 'fields' ] );
            }
            SPFactory::db()->delete( 'spdb_sprr_rating', array( 'rid' => $rid ) );
            SPFactory::db()->update( 'spdb_sprr_review', $rev, array( 'rid' => $rid ) );
        }
        try {
            if ( count( $data[ 'rating' ][ 'fields' ] ) ) {
                foreach ( $data[ 'rating' ][ 'fields' ] as $fid => $rate ) {
                    $data[ 'rating' ][ 'fields' ][ $fid ][ 'rid' ] = $rid;
                    $data[ 'rating' ][ 'fields' ][ $fid ][ 'sid' ] = $this->sid;
                    $data[ 'rating' ][ 'fields' ][ $fid ][ 'state' ] = Sobi::Can( 'review.manage.own' ) || Sobi::Can( 'review.autopublish.own' );
                }
                SPFactory::db()->insertArray( 'spdb_sprr_rating', $data[ 'rating' ][ 'fields' ] );
            }
        }
        catch ( SPException $x ) {
            Sobi::Error( __CLASS__, SPLang::e( 'Cannot save review. Msg %s', $x->getMessage() ), SPC::WARNING, 0, __LINE__, __FILE__ );
            throw new SPException( SPReview::Txt( 'ERR_CANNOT_STORE' ) );
        }
        $this->load( $rid );
        $this->trigger( $action );
        return true;
    }

    private function recount( $full = true )
    {
        $data = array();
        $r = SPFactory::db()->select( array( 'fid', 'vote' ), 'spdb_sprr_rating', array( 'rid' => $this->rid ) )->loadAssocList( 'fid' );
        if ( count( $r ) ) {
            foreach ( $r as $fid => $vote ) {
                $rating[ $fid ] = $vote[ 'vote' ];
            }
        }
        $data[ 'review' ][ 'sid' ] = $this->sid;
        $data[ 'rating' ] =& $rating;
        $this->countRating( $data );

        foreach ( $data[ 'rating' ][ 'fields' ] as $fid => $vote ) {
            $this->rating[ $fid ] = $vote;
        }
        $this->oar = $data[ 'rating' ][ 'oar' ];
        // only if for example a field has been completely deleted
        if ( $full ) {
            SPFactory::db()->delete( 'spdb_sprr_rating', array( 'rid' => $this->rid ) );
            foreach ( $data[ 'rating' ] as $fid => $rate ) {
                $data[ 'rating' ][ 'fields' ][ $fid ][ 'rid' ] = $this->rid;
                $data[ 'rating' ][ 'fields' ][ $fid ][ 'sid' ] = $this->sid;
                $data[ 'rating' ][ 'fields' ][ $fid ][ 'state' ] = $this->state;
            }
            //SPFactory::db()->delete( 'spdb_sprr_rating', array( 'rid' => $this->rid ) );
            SPFactory::db()->insertArray( 'spdb_sprr_rating', $data[ 'rating' ][ 'fields' ] );
        }
        $rev[ 'oar' ] = $this->oar;
        $rev[ 'rRating' ] = serialize( $data[ 'rating' ][ 'fields' ] );
        SPFactory::db()->update( 'spdb_sprr_review', $rev, array( 'rid' => $this->rid ) );
    }

    public function countRating( &$data )
    {
        if ( !( $this->ratingEnabled ) ) {
            $data = array();
            return true;
        }
        $fields = $this->reviewFields();
        $rating = array();
        $oai = 0;
        $fieldsCount = 0;
        foreach ( $fields as $fid => $field ) {
            $fieldsCount++;
            // we have to revert the importance as 1 is the most and 10 is the less important
            $oai += ( 11 - $field[ 'importance' ] );
        }
        if ( !( $oai ) ) {
            $oai = count( $fields );
        }
        $overAll = 0;
        foreach ( $fields as $fid => $field ) {
            $fr = array();
            $fr[ 'sid' ] = $data[ 'review' ][ 'sid' ];
            $fr[ 'fid' ] = $fid;
            $fr[ 'vote' ] = isset( $data[ 'rating' ][ $fid ] ) ? $data[ 'rating' ][ $fid ] : 0;
            $percent = ( 11 - $field[ 'importance' ] ) * 100 / $oai;
            $fr[ 'oai' ] = ( $percent * $fr[ 'vote' ] ) / 100;
            $overAll = $overAll + $fr[ 'oai' ];
            $data[ 'rating' ][ 'fields' ][ $fid ] = $fr;
        }
        $data[ 'rating' ][ 'oar' ] = $overAll;
    }

    /**
     *
     */
    public function validateInput( &$data, $update = false )
    {
        if ( !( Sobi::My( 'id' ) || $update ) || isset( $data[ 'review' ][ 'vmail' ] ) ) {
            if ( $this->revMailRequ ) {
                if ( !( $data[ 'review' ][ 'vmail' ] ) ) {
                    throw new SPException( SPReview::Txt( 'ERR_MAIL_REQ' ) );
                }
                else {
                    $registry =& SPFactory::registry();
                    $registry->loadDBSection( 'fields_filter' );
                    $filter = $registry->get( 'fields_filter.email' );
                    if ( !( preg_match( base64_decode( $filter[ 'params' ] ), $data[ 'review' ][ 'vmail' ] ) ) ) {
                        throw new SPException( str_replace( '$field', '"' . SPReview::Txt( 'FORM_VISITOR_MAIL' ) . '"', SPLang::e( $filter[ 'description' ] ) ) );
                    }
                }
            }
        }
        elseif ( !( $update ) ) {
            $data[ 'review' ][ 'vmail' ] = Sobi::My( 'email' );
            $data[ 'review' ][ 'visitor' ] = Sobi::My( 'name' );
        }
        if ( $this->revEnabled || $update ) {
            if ( $this->ratingRevReq ) {
                if ( !( strlen( $data[ 'review' ][ 'title' ] ) && strlen( $data[ 'review' ][ 'review' ] ) ) ) {
                    throw new SPException( SPReview::Txt( 'ERR_REV_REQ' ) );
                }
            }
            else {
                if ( strlen( $data[ 'review' ][ 'title' ] ) && !( strlen( $data[ 'review' ][ 'review' ] ) ) ) {
                    throw new SPException( SPReview::Txt( 'ERR_REV_NO_REV' ) );
                }
                elseif ( strlen( $data[ 'review' ][ 'review' ] ) && !( strlen( $data[ 'review' ][ 'title' ] ) ) ) {
                    throw new SPException( SPReview::Txt( 'ERR_REV_NO_TITLE' ) );
                }
            }
            if ( !( $this->revPositive || $update ) ) {
                $data[ 'review' ][ 'pos_review' ] = null;
                $data[ 'review' ][ 'neg_review' ] = null;
            }
        }
        else {
            $data[ 'review' ][ 'title' ] = null;
            $data[ 'review' ][ 'review' ] = null;
        }
        if ( count( $data[ 'rating' ] ) ) {
            foreach ( $data[ 'rating' ] as $fid => $value ) {
                if ( !( is_numeric( $fid ) ) || !( is_numeric( $value ) ) ) {
                    throw new SPException( SPReview::Txt( 'ERR_RATING_HACK' ) );
                }
                if ( $value == 0 ) {
                    throw new SPException( SPReview::Txt( 'ERR_RATING_ZERO' ) );
                }
            }
        }
    }

    /**
     *
     */
    public function reviewFieldsView()
    {
        $f = $this->reviewFields();
        $fields = array();
        foreach ( $f as $field ) {
            $fields[ ] = array(
                '_complex' => 1,
                '_data' => array(
                    'label' => $field[ 'label' ],
                    'explanation' => $field[ 'explanation' ],
                ),
                '_attributes' => array(
                    'id' => $field[ 'id' ],
                    'enabled' => $field[ 'enabled' ],
                    'importance' => $field[ 'importance' ],
                    'position' => $field[ 'position' ],
                ),
            );
        }
        return $fields;
    }

    /**
     *
     */
    public function setList( &$entry )
    {
        $rs = $this->countAverage();
        $ratings = $this->reviewFields();
        $entry[ 'reviews' ][ 'summary_rating' ][ 'overall' ] = array( '_complex' => 1, '_data' => round( $rs[ 'oar' ], 2 ), '_attributes' => array( 'count' => $rs[ 'count' ], 'value' => $rs[ 'oar' ] ) );
        if ( isset( $rs[ 'detailed' ] ) && count( $rs[ 'detailed' ] ) ) {
            $r = array();
            foreach ( $ratings as $rating ) {
                if ( isset( $rs[ 'detailed' ][ $rating[ 'fid' ] ] ) ) {
                    $r[ ] = array(
                        '_complex' => 1,
                        '_data' => round( $rs[ 'detailed' ][ $rating[ 'fid' ] ][ 'ar' ], 2 ),
                        '_attributes' => array( 'id' => $rating[ 'fid' ], 'label' => $rating[ 'label' ], 'count' => $rs[ 'detailed' ][ $rating[ 'fid' ] ][ 'count' ], 'value' => $rs[ 'detailed' ][ $rating[ 'fid' ] ][ 'ar' ] )
                    );
                }
            }
            $entry[ 'reviews' ][ 'summary_rating' ][ 'fields' ] = $r;
        }
    }

    /**
     *
     */
    public function storeField( $data )
    {
        SPFactory::db()->insertUpdate(
            'spdb_sprr_fields',
            array(
                'fid' => $data[ 'fid' ],
                'enabled' => $data[ 'enabled' ],
                'importance' => $data[ 'importance' ],
                'sid' => $data[ 'sid' ],
                'position' => $data[ 'position' ],
            )
        );
        if ( !( $data[ 'fid' ] ) ) {
            $data[ 'fid' ] = SPFactory::db()->insertid();
        }
        $data[ 'id' ] = $data[ 'fid' ];
        $data[ 'value' ] = $data[ 'name' ];
        $data[ 'key' ] = $data[ 'type' ] = 'sprr_field';
        SPLang::saveValues( $data );
    }

    /**
     *
     */
    public function reviewFields()
    {
        static $fields = null;
        if ( !( $fields ) ) {
            $query = array( 'sid' => Sobi::Section() );
            if ( !( defined( 'SOBIPRO_ADM' ) ) ) {
                $query[ 'enabled' ] = 1;
            }
            $fields = SPFactory::db()
                    ->select( '*', 'spdb_sprr_fields', $query, 'position' )
                    ->loadAssocList( 'fid' );
            if ( count( $fields ) ) {
                $ids = array_keys( $fields );
                $labels = SPFactory::db()
                        ->select(
                    array( 'sValue', 'explanation', 'language', 'id' ),
                    'spdb_language',
                    array( 'id' => $ids, 'oType' => 'sprr_field', 'section' => Sobi::Section() )
                )->loadAssocList();
                foreach ( $fields as $id => $field ) {
                    foreach ( $labels as $label ) {
                        if ( $label[ 'id' ] == $id ) {
                            if ( !( isset( $field[ 'label' ] ) ) || $label[ 'language' ] == Sobi::Lang() ) {
                                $field[ 'label' ] = $label[ 'sValue' ];
                                $field[ 'explanation' ] = $label[ 'explanation' ];
                            }
                        }
                        $field[ 'id' ] = $id;
                        /* Needed in admin area only */
                        if ( defined( 'SOBIPRO_ADM' ) ) {
                            $row = new SPObject();
                            $row->castArray( $field );
                            $field[ 'object' ] = $row;
                        }
                        $fields[ $id ] = $field;
                    }
                    if ( !( isset( $field[ 'label' ] ) ) ) {
                        $field[ 'label' ] = 'Missing';
                        $field[ 'explanation' ] = 'Missing';
                    }
                }
            }
            else {
                $fields = array();
            }
        }
        return $fields;
    }

    /**
     *
     */
    private function trigger( $action )
    {
        Sobi::Trigger( 'Review', 'After' . ucfirst( $action ) . 'Review', array( array( 'review_data' => &$this ) ) );
    }

    /**
     *
     */
    public function approve()
    {
        SPFactory::db()->update( 'spdb_sprr_review',
            array( 'approved' => Sobi::My( 'id' ), 'appAt' => SPRequest::now(), 'appIP' => SPRequest::ip( 'REMOTE_ADDR', 0, 'SERVER' ), 'state' => 1 ),
            array( 'rid' => $this->rid )
        );
        SPFactory::db()->update( 'spdb_sprr_rating', array( 'state' => 1 ), array( 'rid' => $this->rid ) );
        $this->trigger( __FUNCTION__ );
    }

    /**
     *
     */
    public function unapprove()
    {
        SPFactory::db()->update( 'spdb_sprr_review', array( 'approved' => 0 ), array( 'rid' => $this->rid ) );
        SPFactory::db()->update( 'spdb_sprr_rating', array( 'state' => 0 ), array( 'rid' => $this->rid ) );
        $this->trigger( __FUNCTION__ );
    }

    /**
     *
     */
    public function publish()
    {
        SPFactory::db()->update( 'spdb_sprr_review', array( 'state' => 1 ), array( 'rid' => $this->rid ) );
        SPFactory::db()->update( 'spdb_sprr_rating', array( 'state' => 1 ), array( 'rid' => $this->rid ) );
        $this->trigger( __FUNCTION__ );
    }

    /**
     *
     */
    public function unpublish()
    {
        SPFactory::db()->update( 'spdb_sprr_review', array( 'state' => 0 ), array( 'rid' => $this->rid ) );
        SPFactory::db()->update( 'spdb_sprr_rating', array( 'state' => 0 ), array( 'rid' => $this->rid ) );
        $this->trigger( __FUNCTION__ );
    }

    /**
     *
     */
    public static function Txt( $txt )
    {
        return Sobi::Txt( 'SPRRA.' . $txt );
    }
}
