<?php
/**
 * @version: $Id: contact.php 2572 2012-07-09 15:42:08Z Radek Suski $
 * @package: SobiPro - Contact Form Field
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
 * $Date: 2012-07-09 17:42:08 +0200 (Mo, 09 Jul 2012) $
 * $Revision: 2572 $
 * $Author: Radek Suski $
 */

defined( 'SOBIPRO' ) || exit( 'Restricted access' );
SPLoader::loadClass( 'opt.fields.inbox' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created 06-Sep-2011 12:43:13
 */
class SPField_ContactForm extends SPFieldType implements SPFieldInterface
{
    /**
     * @var string
     */
    protected $cssClass = '';
    /**
     * @var string
     */
    protected $message = '';
    /**
     * @var string
     */
    protected $template = 'contact.xsl';
    /**
     * @var array
     */
    protected $userGroups = array();
    /**
     * @var int
     */
    protected $width = 30;
    /**
     * @var bool
     */
    public $isOutputOnly = true;
    /**
     * @var bool
     */
    public $captcha = 0;
    /**
     * @var bool
     */
    public $html = 0;
    /**
     * @var array
     */
    protected static $switchIO = array( 'entry.edit', 'entry.add', 'entry.save', 'entry.apply', 'entry.clone' );
    /**
     * @var string
     */
    protected $buttonLabel = 'Contact';
    /**
     * @var boolean
     */
    protected $showButton = true;

    /**
     * Returns the parameter list
     * @return array
     */
    protected function getAttr()
    {
        $attr = get_class_vars( __CLASS__ );
        return array_keys( $attr );
    }

    public function __construct( &$field )
    {
        parent::__construct( $field );
        $this->set( 'template', $this->template );
        $this->isOutputOnly = true;
        SPLang::load( 'SpApp.contact' );
    }

    /**
     * Shows the field in the edit entry or add entry form
     * @param bool $return return or display directly
     * @return string
     */
    public function field( $return = false )
    {
        $field = null;
        if ( !$return ) {
            echo $field;
        }
        else {
            return $field;
        }
    }

    /**
     * @param SPEntry $entry
     * @param string $request
     * @return string
     */
    private function verify( $entry, $request )
    {
    }


    /**
     * Gets the data for a field and save it in the database
     * @param SPEntry $entry
     * @return bool
     */
    public function saveData( &$entry, $request = 'POST' )
    {

    }

    public function provide( $action )
    {
        return $action == 'TemplateEngineLoadStyle';
    }


    public function submit( &$entry, $tsid = null, $request = 'POST' )
    {
        return true;
    }

    public function searchForm( $return = false )
    {
        return true;
    }

    /**
     * @param DOMDocument $style
     */
    public function TemplateEngineLoadStyle( &$style )
    {
        $stylesheet = $style
                ->getElementsByTagName( 'stylesheet' )
                ->item( 0 );
        $template = $style
                ->getElementsByTagName( 'template' )
                ->item( 0 );

        $include = $style->createElementNS( 'http://www.w3.org/1999/XSL/Transform', 'xsl:include' );
        $href = $style->createAttribute( 'href' );
        $href->value = '../contact_form/' . $this->template;
        $include->appendChild( $href );
        $stylesheet->appendChild( $include );
        if ( $this->showButton ) {
            $apply = $style->createElementNS( 'http://www.w3.org/1999/XSL/Transform', 'xsl:call-template' );
            $select = $style->createAttribute( 'name' );
            $select->value = str_replace( '.xsl', null, $this->template );
            $apply->appendChild( $select );
            $param = $style->createElementNS( 'http://www.w3.org/1999/XSL/Transform', 'xsl:with-param' );
            $paramName = $style->createAttribute( 'name' );
            $paramName->value = 'field';
            $param->appendChild( $paramName );
            $paramValue = $style->createAttribute( 'select' );
            $paramValue->value = '/entry_details/entry/fields/' . $this->nid . '/data';
            $param->appendChild( $paramValue );
            $apply->appendChild( $param );
            $template->appendChild( $apply );
        }
    }

    public function loadMessage()
    {
        $this->message = SPFactory::registry()
                ->loadDBSection( $this->nid . '_' . Sobi::Section() )
                ->get( $this->nid . '_' . Sobi::Section() . '.' . $this->nid . '.params' );
        if ( !( $this->message ) ) {
            $this->message = array(
                'from' => '{message.name}',
                'fromMail' => '{message.email}',
                'to' => '{author.name}',
                'toMail' => '{author.email}',
                'subject' => 'Contact request from your entry at {cfg:site_name}',
                'body' => '{message.content}',
                'cc' => null,
                'bcc' => null,
                'html' => true,
                'enabled' => true
            );
        }
        else {
            $this->message = SPConfig::unserialize( $this->message );
            $this->message[ 'subject' ] = SPLang::getValue( $this->nid . '.subject', 'application', Sobi::Section() );
            $this->message[ 'body' ] = SPLang::getValue( $this->nid . '.body', 'application', Sobi::Section() );
        }
        $this->message[ 'enabled' ] = true;
        $this->message[ 'sid' ] = SPRequest::sid();
        return $this->message;
    }


    private function checkPerms()
    {
        /* handle permissions */
        $allowed = false;
        $userGroups = Sobi::My( 'gid' );
        if ( !( SPFactory::user()->isAdmin() ) ) {
            if ( count( $userGroups ) ) {
                foreach ( $userGroups as $gid ) {
                    if ( in_array( $gid, $this->userGroups ) ) {
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

    /**
     * @return array
     */
    public function struct()
    {
        $data = array();
        if ( $this->checkPerms() && SPRequest::task() == 'entry.details' ) {
            $this->cssClass = strlen( $this->cssClass ) ? $this->cssClass : 'spFieldsData';
            $this->cssClass = $this->cssClass . ' ' . $this->nid;
            $this->cleanCss();
            SPFactory::header()
                    ->addJsFile( array( 'jquery', 'jquery-ui', 'contact_form' ) )
                    ->addJsCode( "SPContactForm( '{$this->nid}_button' );" )
                    ->addCssFile( 'contact_form' )
                    ->addCssFile( 'jquery-ui.' . Sobi::Cfg( 'jquery.ui_theme', 'smoothness.smoothness' ) );
            Sobi::RegisterHandler( 'entry.details', $this );
            if ( $this->showButton ) {
                $button = array( 'input' => array(
                    '_complex' => 1,
                    '_attributes' =>
                    array(
                        'id' => $this->nid . '_button',
                        'type' => 'button',
                        'value' => $this->buttonLabel,
                        'class' => $this->cssClass
                    )
                ) );
            }
            else {
                $button = null;
            }

            $data = array(
                '_complex' => 1,
                '_data' => $button,
                '_attributes' => array(
                    'type' => 'contact_form',
                    'token' => SPFactory::mainframe()->token(),
                    'id' => $this->id,
                    'nid' => $this->nid
                )
            );
        }
        return $data;
    }

    /**
     * @return bool
     */
    public function searchString( $data, $section )
    {
        return true;
    }

    public function searchData( $request, $section )
    {
        return true;
    }
}
