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
SPLoader::loadClass( 'opt.fields.contact' );
/**
 * @author Radek Suski
 * @version 1.0
 * @created Mon, Jun 4, 2012
 */
class SPField_AdmContactForm extends SPField_ContactForm implements SPFieldInterface
{
    public function onFieldEdit( &$view )
    {
        SPLang::load( 'SpApp.notifications' );
        /**
         * Get available user groups
         */
        $get = SPFactory::Controller( 'acl', true )
                ->userGroups();
        $groups = array();
        foreach ( $get as $group ) {
            $groups[ $group[ 'value' ] ] = $group[ 'text' ];
        }
        $view->assign( $groups, 'userGroups' );

        /**
         * Get available templates
         */
        $currentTpl = Sobi::Cfg( 'section.template' );
        $currentTpl = SOBI_PATH . "/usr/templates/{$currentTpl}/contact_form/";
        if ( !( file_exists( $currentTpl ) ) || ( count( scandir( $currentTpl ) ) < 3 ) ) {
            $this->installTpl( $currentTpl );
            $files = scandir( $currentTpl );
        }
        $files = scandir( $currentTpl );
        if ( count( $files ) ) {
            foreach ( $files as $file ) {
                $stack = explode( '.', $file );
                if ( array_pop( $stack ) == 'xsl' ) {
                    $tpls[ $file ] = $file;
                }
            }
        }
        $view->assign( $tpls, 'templates' );

        /**
         * Get message
         */
        $message = $this->loadMessage();
        $view->assign( $message, 'message' );
    }

    private function installTpl( $target )
    {
        SPFactory::Instance( 'base.fs.directory', SOBI_PATH . '/usr/templates/default/contact_form/' )
                ->copy( $target );
    }

    public function save( &$attr )
    {
        $values = SPRequest::search( 'field_message' );
        $settings = array(
            'from' => $values[ 'field_message_from' ],
            'fromMail' => $values[ 'field_message_fromMail' ],
            'to' => $values[ 'field_message_to' ],
            'toMail' => $values[ 'field_message_toMail' ],
            'cc' => $values[ 'field_message_cc' ],
            'bcc' => $values[ 'field_message_bcc' ],
            'html' => $values[ 'field_message_html' ],
        );
        SPFactory::registry()
                ->saveDBSection( array( array( 'key' => $this->nid, 'params' => $settings, 'value' => date( DATE_RFC822 ) ) ), $this->nid . '_' . Sobi::Section() );
        try {
            $data = array(
                'key' => $this->nid . '.subject',
                'value' => $values[ 'field_message_subject' ],
                'type' => 'application',
                'id' => Sobi::Section(),
                'section' => Sobi::Section()
            );
            SPLang::saveValues( $data );
            $data[ 'key' ] = $this->nid . '.body';
            $data[ 'value' ] = $values[ 'field_message_body' ];
            SPLang::saveValues( $data );
        } catch ( SPException $x ) {
            $msg = SPLang::e( 'DB_REPORTS_ERR', $x->getMessage() );
            Sobi::Error( $this->nid, $msg, SPC::WARNING, 0, __LINE__, __FILE__ );
        }
        parent::save( $attr );
    }
}
